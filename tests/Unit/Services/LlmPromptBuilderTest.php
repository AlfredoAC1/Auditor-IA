<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\LLM\LlmPromptBuilder;
use PHPUnit\Framework\TestCase;

class LlmPromptBuilderTest extends TestCase
{
    private LlmPromptBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new LlmPromptBuilder;
    }

    public function test_build_system_prompt_returns_context_and_instructions(): void
    {
        foreach (AnalysisType::cases() as $analysisType) {
            $prompt = $this->builder->buildSystemPrompt($analysisType);

            $this->assertStringContainsString('Eres un asistente legal especializado en legislación chilena.', $prompt);
            $this->assertStringContainsString($analysisType->label(), $prompt);
        }
    }

    public function test_build_user_prompt_includes_document_context_and_content(): void
    {
        $document = new Document([
            'original_filename' => 'contrato.pdf',
        ]);

        $prompt = $this->builder->buildUserPrompt(
            'Contenido del contrato de arriendo.',
            AnalysisType::FullSummary,
            $document,
        );

        $this->assertStringContainsString('Nombre del archivo: contrato.pdf', $prompt);
        $this->assertStringContainsString('Contenido del contrato de arriendo.', $prompt);
        $this->assertStringContainsString(AnalysisType::FullSummary->label(), $prompt);
    }

    public function test_build_contextual_user_prompt_includes_prior_analysis_and_supporting_docs(): void
    {
        $primaryDocument = new Document([
            'original_filename' => 'contrato_principal.pdf',
        ]);

        $analysis = new DocumentAnalysis([
            'content' => [
                'resumen_general' => 'Resumen previo del contrato',
                'tipo_documento' => 'Contrato de Arriendo',
                'nivel_confianza' => 'alto',
            ],
        ]);

        $supportingParts = [
            [
                'filename' => 'anexo1.pdf',
                'content' => 'Contenido del anexo 1',
            ],
        ];

        $prompt = $this->builder->buildContextualUserPrompt(
            $primaryDocument,
            'Contenido principal',
            $analysis,
            $supportingParts,
            AnalysisType::ContextualSummary,
        );

        $this->assertStringContainsString('DOCUMENTO PRINCIPAL: contrato_principal.pdf', $prompt);
        $this->assertStringContainsString('Resumen previo: Resumen previo del contrato', $prompt);
        $this->assertStringContainsString('DOCUMENTO DE APOYO #1: anexo1.pdf', $prompt);
        $this->assertStringContainsString('Contenido del anexo 1', $prompt);
    }
}
