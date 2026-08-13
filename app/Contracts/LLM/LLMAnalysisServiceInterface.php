<?php

declare(strict_types=1);

namespace App\Contracts\LLM;

use App\Enums\AnalysisType;
use App\Models\Document;

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
    ): array;
}
