<?php

declare(strict_types=1);

namespace Tests\Unit\Services\LLM;

use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\LLM\ProLLMAnalysisService;
use App\Services\LLM\ProLlmPromptBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Unit tests for ProLLMAnalysisService on Qwen Cloud.
 *
 * GUARANTEE: All HTTP calls are strictly intercepted with Http::fake().
 * Absolutely ZERO network calls are made to paid endpoints.
 */
class ProLLMAnalysisServiceTest extends TestCase
{
    private ProLLMAnalysisService $service;

    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'llm.providers.qwen_cloud.api_key' => 'test-qwen-key',
            'llm.providers.qwen_cloud.base_url' => 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1',
            'llm.providers.qwen_cloud.model' => 'qwen3-max',
            'llm.providers.qwen_cloud.max_tokens' => 16384,
            'llm.providers.qwen_cloud.temperature' => 0.3,
            'llm.providers.qwen_cloud.timeout_seconds' => 60,
            'llm.providers.qwen_cloud.tier_models' => [
                'lightweight' => 'qwen3-14b',
                'standard' => 'qwen3-max',
                'advanced' => 'qwen3.5-flash',
                'premium' => 'qwen-3.7-flash',
            ],
            'llm.providers.qwen_cloud.tier_max_tokens' => [
                'lightweight' => 4096,
                'standard' => 8192,
                'advanced' => 16384,
                'premium' => 32768,
            ],
        ]);

        $this->service = new ProLLMAnalysisService(new ProLlmPromptBuilder);

        $this->document = new Document([
            'organization_id' => '018f3a8b-1234-7000-8000-000000000002',
            'original_filename' => 'contrato_pro_test.pdf',
        ]);
        $this->document->id = '018f3a8b-1234-7000-8000-000000000001';
    }

    public function test_throws_exception_if_api_key_is_missing(): void
    {
        config(['llm.providers.qwen_cloud.api_key' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API key is not configured');

        $this->service->analyze(
            documentContent: 'Texto de prueba',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
        );
    }

    public function test_analyzes_with_lightweight_tier_using_qwen3_14b(): void
    {
        Http::fake([
            'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_general' => 'Resumen generado por Qwen3 14B.',
                                'tipo_documento' => 'Contrato Ligero',
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 200,
                    'completion_tokens' => 150,
                ],
            ]),
        ]);

        $result = $this->service->analyze(
            documentContent: 'Contrato de prueba...',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
            overrideModel: 'qwen_cloud::qwen3-14b',
        );

        $this->assertEquals('qwen_cloud', $result['llm_provider']);
        $this->assertEquals('qwen3-14b', $result['llm_model']);
        $this->assertEquals('lightweight', $result['prompt_metadata']['model_tier']);
        $this->assertEquals(200, $result['input_tokens']);
        $this->assertEquals(150, $result['output_tokens']);
        $this->assertEquals('Resumen generado por Qwen3 14B.', $result['content']['resumen_general']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['model'] === 'qwen3-14b'
                && $request->hasHeader('Authorization', 'Bearer test-qwen-key')
                && $data['max_tokens'] === 4096;
        });
    }

    public function test_analyzes_with_standard_tier_using_qwen3_max(): void
    {
        Http::fake([
            'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_general' => 'Auditoría detallada generada por Qwen3 Max.',
                                'tipo_documento' => 'Contrato Comercial',
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 500,
                    'completion_tokens' => 400,
                ],
            ]),
        ]);

        $result = $this->service->analyze(
            documentContent: 'Contrato estándar...',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
            overrideModel: 'qwen_cloud::qwen3-max',
        );

        $this->assertEquals('qwen_cloud', $result['llm_provider']);
        $this->assertEquals('qwen3-max', $result['llm_model']);
        $this->assertEquals('standard', $result['prompt_metadata']['model_tier']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['model'] === 'qwen3-max'
                && $data['max_tokens'] === 8192;
        });
    }

    public function test_analyzes_with_advanced_tier_using_qwen3_5_flash(): void
    {
        Http::fake([
            'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_general' => 'Auditoría de asimetría por Qwen3.5 Flash.',
                                'tipo_documento' => 'Acuerdo Marco',
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 800,
                    'completion_tokens' => 700,
                ],
            ]),
        ]);

        $result = $this->service->analyze(
            documentContent: 'Contrato avanzado...',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
            overrideModel: 'qwen3.5-flash',
        );

        $this->assertEquals('advanced', $result['prompt_metadata']['model_tier']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['model'] === 'qwen3.5-flash'
                && $data['max_tokens'] === 16384;
        });
    }

    public function test_analyzes_with_premium_tier_using_qwen_3_7_flash(): void
    {
        Http::fake([
            'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'briefing_ejecutivo_gerencial' => 'Auditoría forense completa por Qwen-3.7 Flash.',
                                'tipo_documento' => 'Contrato de Concesión',
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 1500,
                    'completion_tokens' => 1200,
                ],
            ]),
        ]);

        $result = $this->service->analyze(
            documentContent: 'Contrato premium...',
            analysisType: AnalysisType::FullSummary,
            document: $this->document,
            overrideModel: 'qwen-3.7-flash',
        );

        $this->assertEquals('premium', $result['prompt_metadata']['model_tier']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['model'] === 'qwen-3.7-flash'
                && $data['max_tokens'] === 32768;
        });
    }

    public function test_analyze_with_context_executes_successfully(): void
    {
        Http::fake([
            'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_integrado' => 'Análisis conjunto de contrato y anexo.',
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 600,
                    'completion_tokens' => 300,
                ],
            ]),
        ]);

        $priorAnalysis = new DocumentAnalysis([
            'content' => ['resumen_general' => 'Resumen base'],
        ]);
        $priorAnalysis->id = '018f3a8b-1234-7000-8000-000000000003';

        $supportingDoc = new Document([
            'original_filename' => 'anexo1.pdf',
            'metadata' => ['parsed_compact_content' => 'Contenido anexo'],
        ]);

        $result = $this->service->analyzeWithContext(
            primaryDocument: $this->document,
            existingAnalysis: $priorAnalysis,
            supportingDocuments: new Collection([$supportingDoc]),
            analysisType: AnalysisType::ContextualSummary,
            overrideModel: 'qwen3-max',
        );

        $this->assertEquals('qwen_cloud', $result['llm_provider']);
        $this->assertEquals('Análisis conjunto de contrato y anexo.', $result['content']['resumen_integrado']);
    }
}
