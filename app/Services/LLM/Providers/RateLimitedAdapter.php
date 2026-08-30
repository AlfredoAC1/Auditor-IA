<?php

declare(strict_types=1);

namespace App\Services\LLM\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate-limited adapter for providers that impose strict RPM caps.
 *
 * Extends the generic OpenAI-compatible adapter and adds:
 * - Pre-request local RPM enforcement (backed by Laravel's RateLimiter/Redis)
 * - Brief auto-pause for windows ≤ 2 s to avoid unnecessary failures
 * - Extra hit on 429 responses to prevent thundering herd retries
 *
 * Currently used for: NVIDIA NIM (40 RPM default).
 * Can also be activated for Groq free-tier (30 RPM) or any other
 * provider by setting rateLimitRpm in config/llm.php.
 */
class RateLimitedAdapter extends OpenAiCompatibleAdapter
{
    public function providerSlug(): string
    {
        return 'rate_limited';
    }

    /**
     * @return array{raw_content: string, input_tokens: int, output_tokens: int, processing_time_ms: int}
     *
     * @throws \RuntimeException
     */
    public function complete(
        string $systemPrompt,
        string $userPrompt,
        LlmProviderConfig $config,
        ?string $documentId = null,
    ): array {
        $rateLimitKey = "llm:{$config->provider}:rate_limit";

        // Enforce local RPM before hitting the API
        if ($config->rateLimitRpm > 0) {
            $this->enforceRateLimit($rateLimitKey, $config->rateLimitRpm, $documentId);
            RateLimiter::hit($rateLimitKey, 60);
        }

        try {
            return parent::complete($systemPrompt, $userPrompt, $config, $documentId);
        } catch (\RuntimeException $e) {
            // Extra hit on upstream 429 to slow down retries
            if ($config->rateLimitRpm > 0 && str_contains($e->getMessage(), 'Rate limit reached')) {
                RateLimiter::hit($rateLimitKey, 60);
            }

            throw $e;
        }
    }

    /**
     * @throws \RuntimeException If local rate limit is exceeded with no quick recovery
     */
    private function enforceRateLimit(string $key, int $rpm, ?string $documentId): void
    {
        if (! RateLimiter::tooManyAttempts($key, $rpm)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        Log::warning('LLM rate limit reached locally (pre-request).', [
            'document_id'    => $documentId,
            'rate_limit_key' => $key,
            'rpm_limit'      => $rpm,
            'seconds_to_wait' => $seconds,
        ]);

        // Auto-pause for very short windows (≤ 2 s)
        if ($seconds > 0 && $seconds <= 2) {
            usleep($seconds * 1_000_000);

            if (! RateLimiter::tooManyAttempts($key, $rpm)) {
                return;
            }
        }

        throw new \RuntimeException(
            "RPM limit exceeded ({$rpm} req/min). Please wait {$seconds} second(s) before retrying. Key: [{$key}]."
        );
    }
}
