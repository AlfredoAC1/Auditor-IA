<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AnalysisType;
use App\Models\Document;
use App\Services\LLM\LlmAnalysisService;
use App\Services\LLM\LlmPromptBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Tests LlmAnalysisService when resolved with the nvidia_nim provider.
 *
 * These scenarios exercise the RateLimitedAdapter path
 * (rate_limit_rpm > 0 in config) to validate RPM enforcement
 * and 429 error handling is preserved from the old NvidiaNimAnalysisService.
 */
class NvidiaNimAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    private LlmAnalysisService $service;

    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('llm:nvidia_nim:rate_limit');

        config([
            'llm.default_provider' => 'nvidia_nim',
            'llm.providers.nvidia_nim.api_key' => 'test-nvidia-nim-key',
            'llm.providers.nvidia_nim.model' => 'meta/llama-3.3-70b-instruct',
            'llm.providers.nvidia_nim.base_url' => 'https://integrate.api.nvidia.com/v1',
            'llm.providers.nvidia_nim.rate_limit_rpm' => 40,
            'llm.providers.nvidia_nim.max_tokens' => 4096,
            'llm.providers.nvidia_nim.temperature' => 0.3,
            'llm.providers.nvidia_nim.timeout_seconds' => 60,
        ]);

        $this->service = new LlmAnalysisService(new LlmPromptBuilder);

        $this->document = new Document([
            'id' => '018f3a8b-1234-7000-8000-000000000001',
            'organization_id' => '018f3a8b-1234-7000-8000-000000000002',
            'original_filename' => 'contrato_arrendamiento.pdf',
        ]);
    }

    public function test_throws_exception_if_api_key_is_missing(): void
    {
        config(['llm.providers.nvidia_nim.api_key' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API key is not configured');

        $this->service->analyze(
            documentContent: 'Texto de prueba',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
            overrideModel: 'nvidia_nim::meta/llama-3.3-70b-instruct',
        );
    }

    public function test_analyze_executes_successfully_and_returns_structured_result(): void
    {
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_general' => 'Resumen del contrato generado por NVIDIA NIM.',
                                'tipo_documento' => 'Contrato Legal',
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 350,
                    'completion_tokens' => 200,
                ],
            ], 200),
        ]);

        $result = $this->service->analyze(
            documentContent: 'Contenido del contrato...',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
            overrideModel: 'nvidia_nim::meta/llama-3.3-70b-instruct',
        );

        $this->assertEquals('nvidia_nim', $result['llm_provider']);
        $this->assertEquals('meta/llama-3.3-70b-instruct', $result['llm_model']);
        $this->assertEquals(350, $result['input_tokens']);
        $this->assertEquals(200, $result['output_tokens']);
        $this->assertEquals('Resumen del contrato generado por NVIDIA NIM.', $result['content']['resumen_general']);
    }

    public function test_handles_429_too_many_requests_rate_limit_error(): void
    {
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'detail' => 'Too Many Requests',
            ], 429, ['Retry-After' => '10']),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rate limit reached');

        $this->service->analyze(
            documentContent: 'Texto corto',
            analysisType: AnalysisType::RiskAssessment,
            document: $this->document,
            overrideModel: 'nvidia_nim::meta/llama-3.3-70b-instruct',
        );
    }

    public function test_enforces_local_40_rpm_rate_limit(): void
    {
        // Hit rate limiter up to max RPM limit (40 times)
        for ($i = 0; $i < 40; $i++) {
            RateLimiter::hit('llm:nvidia_nim:rate_limit', 60);
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RPM limit exceeded');

        $this->service->analyze(
            documentContent: 'Texto para exceder limite',
            analysisType: AnalysisType::ClauseAnalysis,
            document: $this->document,
            overrideModel: 'nvidia_nim::meta/llama-3.3-70b-instruct',
        );
    }
}
