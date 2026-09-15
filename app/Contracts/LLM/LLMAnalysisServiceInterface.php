<?php

declare(strict_types=1);

namespace App\Contracts\LLM;

use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface contract for LLM document analysis providers.
 */
interface LLMAnalysisServiceInterface
{
    /**
     * Analyze document content and return structured analysis with metadata.
     *
     * @param  string  $documentContent  Compact text extracted from the document
     * @param  AnalysisType  $analysisType  Type of analysis to execute
     * @param  Document  $document  Document instance for contextual tracing
     * @param  string|null  $overrideModel  Optional model override
     * @return array{
     *     content: array<string, mixed>,
     *     llm_provider: string,
     *     llm_model: string,
     *     input_tokens: int,
     *     output_tokens: int,
     *     processing_time_ms: int,
     *     prompt_metadata: array<string, mixed>,
     * }
     *
     * @throws \RuntimeException If API execution fails or rate limit is reached
     */
    public function analyze(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
        ?string $overrideModel = null,
    ): array;

    /**
     * Analyze a document enriched with supporting documents and a prior analysis.
     *
     * @param  Document  $primaryDocument  Primary source document
     * @param  DocumentAnalysis  $existingAnalysis  Prior baseline analysis record
     * @param  Collection<int, Document>  $supportingDocuments  Collection of supporting documents
     * @param  AnalysisType  $analysisType  Type of analysis to execute
     * @param  string|null  $overrideModel  Optional model override
     * @return array{
     *     content: array<string, mixed>,
     *     llm_provider: string,
     *     llm_model: string,
     *     input_tokens: int,
     *     output_tokens: int,
     *     processing_time_ms: int,
     *     prompt_metadata: array<string, mixed>,
     *     skipped_documents: array<string>,
     * }
     *
     * @throws \RuntimeException If API execution fails or rate limit is reached
     */
    public function analyzeWithContext(
        Document $primaryDocument,
        DocumentAnalysis $existingAnalysis,
        Collection $supportingDocuments,
        AnalysisType $analysisType = AnalysisType::ContextualSummary,
        ?string $overrideModel = null,
    ): array;
}
