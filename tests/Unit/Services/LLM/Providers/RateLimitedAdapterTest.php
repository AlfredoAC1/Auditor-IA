<?php

declare(strict_types=1);

namespace Tests\Unit\Services\LLM\Providers;

use App\Services\LLM\Providers\LlmProviderConfig;
use App\Services\LLM\Providers\RateLimitedAdapter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitedAdapterTest extends TestCase
{
    private RateLimitedAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = new RateLimitedAdapter;
        RateLimiter::clear('llm:test_provider:rate_limit');
    }

    private function makeConfig(int $rateLimitRpm = 10): LlmProviderConfig
    {
        return new LlmProviderConfig(
            provider: 'test_provider',
            apiKey: 'secret',
            model: 'model-a',
            baseUrl: 'https://api.test.com/v1',
            maxTokens: 1000,
            temperature: 0.7,
            timeoutSeconds: 30,
            rateLimitRpm: $rateLimitRpm,
        );
    }

    public function test_provider_slug_returns_rate_limited(): void
    {
        $this->assertEquals('rate_limited', $this->adapter->providerSlug());
    }

    public function test_complete_hits_rate_limiter_and_returns_result_on_success(): void
    {
        Http::fake([
            'https://api.test.com/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"success": true}']],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 20,
                ],
            ], 200),
        ]);

        $config = $this->makeConfig(rateLimitRpm: 10);

        $result = $this->adapter->complete('system', 'user', $config, 'doc-123');

        $this->assertEquals('{"success": true}', $result['raw_content']);
        $this->assertEquals(1, RateLimiter::attempts('llm:test_provider:rate_limit'));
    }

    public function test_complete_adds_extra_rate_limiter_hit_when_upstream_returns_rate_limit_error(): void
    {
        Http::fake([
            'https://api.test.com/v1/chat/completions' => Http::response([
                'detail' => 'Too Many Requests',
            ], 429),
        ]);

        $config = $this->makeConfig(rateLimitRpm: 10);

        // Prior to complete(), attempts is 0.
        $this->assertEquals(0, RateLimiter::attempts('llm:test_provider:rate_limit'));

        try {
            $this->adapter->complete('system', 'user', $config, 'doc-123');
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Rate limit reached', $e->getMessage());
        }

        // 1 hit pre-request + 1 extra hit in catch block on 'Rate limit reached' = 2 hits
        $this->assertEquals(2, RateLimiter::attempts('llm:test_provider:rate_limit'));
    }

    public function test_complete_does_not_add_extra_hit_for_non_rate_limit_exception(): void
    {
        Http::fake([
            'https://api.test.com/v1/chat/completions' => Http::response([
                'error' => ['message' => 'Internal server error'],
            ], 500),
        ]);

        $config = $this->makeConfig(rateLimitRpm: 10);

        try {
            $this->adapter->complete('system', 'user', $config, 'doc-123');
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('API returned status [500]', $e->getMessage());
        }

        // 1 hit pre-request only, no extra hit for non-429 error
        $this->assertEquals(1, RateLimiter::attempts('llm:test_provider:rate_limit'));
    }

    public function test_complete_does_not_hit_rate_limiter_when_rate_limit_rpm_is_zero(): void
    {
        Http::fake([
            'https://api.test.com/v1/chat/completions' => Http::response([
                'detail' => 'Too Many Requests',
            ], 429),
        ]);

        $config = $this->makeConfig(rateLimitRpm: 0);

        try {
            $this->adapter->complete('system', 'user', $config, 'doc-123');
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Rate limit reached', $e->getMessage());
        }

        // Rate limit RPM is 0, so no hits pre-request or post-exception
        $this->assertEquals(0, RateLimiter::attempts('llm:test_provider:rate_limit'));
    }

    public function test_enforces_local_rate_limit_pre_request(): void
    {
        $config = $this->makeConfig(rateLimitRpm: 2);

        // Max attempts is 2. Hit twice.
        RateLimiter::hit('llm:test_provider:rate_limit', 60);
        RateLimiter::hit('llm:test_provider:rate_limit', 60);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RPM limit exceeded');

        $this->adapter->complete('system', 'user', $config, 'doc-123');
    }
}
