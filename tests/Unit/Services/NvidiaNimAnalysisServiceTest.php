<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AnalysisType;
use App\Models\Document;
use App\Services\LLM\NvidiaNimAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class NvidiaNimAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('llm:nvidia_nim:rate_limit');

        config([
            'llm.providers.nvidia_nim.api_key' => 'test-nvidia-nim-key',
            'llm.providers.nvidia_nim.model' => 'meta/llama-3.3-70b-instruct',
            'llm.providers.nvidia_nim.base_url' => 'https://integrate.api.nvidia.com/v1',
            'llm.providers.nvidia_nim.rate_limit_rpm' => 40,
        ]);

        $this->document = new Document([
            'id' => '018f3a8b-1234-7000-8000-000000000001',
            'organization_id' => '018f3a8b-1234-7000-8000-000000000002',
            'original_filename' => 'contrato_arrendamiento.pdf',
        ]);
    }

    public function test_throws_exception_if_api_key_is_missing(): void
    {
        config(['llm.providers.nvidia_nim.api_key' => '']);

        $service = new NvidiaNimAnalysisService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NVIDIA NIM API key is not configured');

        $service->analyze(
            documentContent: 'Texto de prueba',
            analysisType: AnalysisType::FullSummary,
            document: $this->document
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

        $service = new NvidiaNimAnalysisService();

        $result = $service->analyze(
            documentContent: 'Contenido del contrato...',
            analysisType: AnalysisType::FullSummary,
            document: $this->document
        );

        $this->assertEquals('nvidia_nim', $result['llm_provider']);
        $this->assertEquals('meta/llama-3.3-70b-instruct', $result['llm_model']);
        $this->assertEquals(350, $result['input_tokens']);
        $this->assertEquals(200, $result['output_tokens']);
        $this->assertEquals('Resumen del contrato generado por NVIDIA NIM.', $result['content']['resumen_general']);
        $this->assertEquals(40, $result['prompt_metadata']['rate_limit_rpm']);
    }

    public function test_handles_429_too_many_requests_rate_limit_error(): void
    {
        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'detail' => 'Too Many Requests',
            ], 429, ['Retry-After' => 10]),
        ]);

        $service = new NvidiaNimAnalysisService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NVIDIA NIM Rate limit reached (40 RPM max limit)');

        $service->analyze(
            documentContent: 'Texto corto',
            analysisType: AnalysisType::RiskAssessment,
            document: $this->document
        );
    }

    public function test_enforces_local_40_rpm_rate_limit(): void
    {
        // Hit rate limiter up to max RPM limit (40 times)
        for ($i = 0; $i < 40; $i++) {
            RateLimiter::hit('llm:nvidia_nim:rate_limit', 60);
        }

        $service = new NvidiaNimAnalysisService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NVIDIA NIM RPM limit exceeded');

        $service->analyze(
            documentContent: 'Texto para exceder limite',
            analysisType: AnalysisType::ClauseAnalysis,
            document: $this->document
        );
    }
}
