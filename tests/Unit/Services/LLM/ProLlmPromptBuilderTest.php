<?php

declare(strict_types=1);

namespace Tests\Unit\Services\LLM;

use App\Enums\AiModelTier;
use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\LLM\ProLlmPromptBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the prompt scaling across AiModelTier levels for Pro users on Qwen Cloud.
 *
 * Validates that prompt complexity, depth, and instruction size expand
 * proportionally to justify credit cost and text generation.
 *
 * NOTE: Pure unit test — NO network calls.
 */
class ProLlmPromptBuilderTest extends TestCase
{
    private ProLlmPromptBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ProLlmPromptBuilder;
    }

    public function test_system_prompt_scales_in_depth_across_tiers(): void
    {
        $lightweightPrompt = $this->builder->buildSystemPrompt(AnalysisType::FullSummary, AiModelTier::Lightweight);
        $standardPrompt = $this->builder->buildSystemPrompt(AnalysisType::FullSummary, AiModelTier::Standard);
        $advancedPrompt = $this->builder->buildSystemPrompt(AnalysisType::FullSummary, AiModelTier::Advanced);
        $premiumPrompt = $this->builder->buildSystemPrompt(AnalysisType::FullSummary, AiModelTier::Premium);

        // All must enforce Chilean legal context
        $this->assertStringContainsString('AuditorIA Pro', $lightweightPrompt);
        $this->assertStringContainsString('República de Chile', $lightweightPrompt);
        $this->assertStringContainsString('Código Civil', $lightweightPrompt);

        // Validate tier directives
        $this->assertStringContainsString('LIGHTWEIGHT', $lightweightPrompt);
        $this->assertStringContainsString('STANDARD', $standardPrompt);
        $this->assertStringContainsString('ADVANCED', $advancedPrompt);
        $this->assertStringContainsString('PREMIUM', $premiumPrompt);

        // Validate scaling: each higher tier must contain more detailed analytical instructions
        $this->assertGreaterThan(strlen($lightweightPrompt), strlen($standardPrompt));
        $this->assertGreaterThan(strlen($standardPrompt), strlen($advancedPrompt));
        $this->assertGreaterThan(strlen($advancedPrompt), strlen($premiumPrompt));

        // Advanced tier must check for contractual asymmetry and loopholes
        $this->assertStringContainsString('analisis_asimetria_contractual', $advancedPrompt);
        $this->assertStringContainsString('vacios_legales_y_ambiguedades', $advancedPrompt);

        // Premium tier must include forensic redlining and scenario simulation
        $this->assertStringContainsString('redlining_clausulas_sugeridas', $premiumPrompt);
        $this->assertStringContainsString('simulacion_escenarios_adversos', $premiumPrompt);
        $this->assertStringContainsString('doctrina_y_jurisprudencia_relevante', $premiumPrompt);
    }

    public function test_user_prompt_includes_depth_directive_and_document_content(): void
    {
        $document = new Document([
            'original_filename' => 'contrato_arriendo_comercial.docx',
        ]);

        $prompt = $this->builder->buildUserPrompt(
            documentContent: 'El presente contrato de arrendamiento se celebra entre...',
            analysisType: AnalysisType::FullSummary,
            document: $document,
            tier: AiModelTier::Premium,
        );

        $this->assertStringContainsString('contrato_arriendo_comercial.docx', $prompt);
        $this->assertStringContainsString('DIRECTIVA DE PROFUNDIDAD (Premium):', $prompt);
        $this->assertStringContainsString('auditoría forense institucional de máxima profundidad', $prompt);
        $this->assertStringContainsString('El presente contrato de arrendamiento se celebra entre...', $prompt);
    }

    public function test_contextual_user_prompt_integrates_prior_analysis_and_supporting_docs(): void
    {
        $primaryDocument = new Document([
            'original_filename' => 'contrato_prestacion_servicios.pdf',
        ]);

        $priorAnalysis = new DocumentAnalysis([
            'content' => [
                'resumen_general' => 'Resumen previo del contrato principal.',
                'tipo_documento' => 'Prestación de Servicios',
                'nivel_confianza' => 'alto',
            ],
        ]);

        $supportingParts = [
            [
                'filename' => 'anexo_tarifas.pdf',
                'content' => 'Tarifas por hora y condiciones de pago.',
            ],
        ];

        $prompt = $this->builder->buildContextualUserPrompt(
            primaryDocument: $primaryDocument,
            primaryContent: 'Contenido del contrato de servicios.',
            existingAnalysis: $priorAnalysis,
            supportingParts: $supportingParts,
            analysisType: AnalysisType::ContextualSummary,
            tier: AiModelTier::Advanced,
        );

        $this->assertStringContainsString('contrato_prestacion_servicios.pdf', $prompt);
        $this->assertStringContainsString('anexo_tarifas.pdf', $prompt);
        $this->assertStringContainsString('Resumen previo del contrato principal.', $prompt);
        $this->assertStringContainsString('Tarifas por hora y condiciones de pago.', $prompt);
        $this->assertStringContainsString('AUDITORÍA CONTEXTUAL INTEGRADA (Avanzado):', $prompt);
    }
}
