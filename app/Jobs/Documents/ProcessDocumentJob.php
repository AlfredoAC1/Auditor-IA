<?php

declare(strict_types=1);

namespace App\Jobs\Documents;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Models\AiModel;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\Documents\DocumentParsingService;
use App\Services\LLM\LlmAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job responsible for orchestrating document processing.
 *
 * This is the entry point for the async processing pipeline.
 * Future implementations will add:
 * - Text extraction (OCR for PDFs, direct for DOCX)
 * - Chunking strategy
 * - Groq API integration
 * - Embedding generation
 * - Audit analysis
 *
 * Currently acts as a stub that marks the document as processed.
 */
class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries;

    /**
     * Backoff intervals in seconds between retries.
     *
     * @var array<int>
     */
    public array $backoff;

    public function __construct(
        public readonly Document $document,
    ) {
        $this->onQueue((string) config('documents.processing.queue', 'documents'));
        $this->tries = (int) config('documents.processing.max_retries', 3);
        $this->backoff = (array) config('documents.processing.retry_backoff_seconds', [30, 120, 600]);
    }

    /**
     * Execute the job.
     */
    public function handle(
        DocumentParsingService $parsingService,
    ): void {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $document = $this->document->fresh() ?? $this->document;

        Log::info('Document processing started.', [
            'document_id' => $document->id,
            'organization_id' => $document->organization_id,
            'mime_type' => $document->mime_type,
            'file_size_bytes' => $document->file_size_bytes,
        ]);

        try {
            $document->beginProcessing();

            // 1. Parsing & Content Extraction
            $compactContent = $document->metadata['parsed_compact_content'] ?? null;

            if ($compactContent === null || $compactContent === '') {
                $parseResult = $parsingService->parse($document);
                $compactContent = $parsingService->toCompactString($parseResult);

                $document->update([
                    'metadata' => array_merge($document->metadata ?? [], [
                        'parsed_elements' => $parseResult['elements'],
                        'parsed_element_count' => $parseResult['element_count'],
                        'parsed_estimated_tokens' => $parseResult['estimated_tokens'],
                        'parsed_compact_content' => $compactContent,
                        'parsed_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            // 2. AI Analysis (Resolve AiModel from metadata or fallback to provider)
            $modelSlug = $document->metadata['model_slug'] ?? null;
            $aiModel = null;

            if ($modelSlug !== null && is_string($modelSlug)) {
                $aiModel = AiModel::query()
                    ->where('slug', $modelSlug)
                    ->where('is_active', true)
                    ->first();
            }

            if ($aiModel === null && isset($document->metadata['llm_provider'])) {
                $requestedProvider = (string) $document->metadata['llm_provider'];
                $aiModel = AiModel::query()
                    ->where('provider', $requestedProvider)
                    ->where('is_active', true)
                    ->ordered()
                    ->first();
            }

            if ($aiModel !== null) {
                $provider = $aiModel->provider;
                $modelIdentifier = "{$aiModel->provider}::{$aiModel->provider_model_id}";
                $modelSlugForRecord = $aiModel->slug;
            } else {
                $provider = (string) ($document->metadata['llm_provider'] ?? config('llm.default_provider', 'cerebras'));
                $configModel = (string) config("llm.providers.{$provider}.model", '');
                $modelIdentifier = $configModel !== '' ? "{$provider}::{$configModel}" : null;
                $modelSlugForRecord = $configModel !== '' ? $configModel : $provider;
            }

            /** @var LlmAnalysisService $llmService */
            $llmService = app(LlmAnalysisService::class);

            try {
                $analysisResult = $llmService->analyze(
                    documentContent: $compactContent,
                    analysisType: AnalysisType::FullSummary,
                    document: $document,
                    overrideModel: $modelIdentifier,
                );

                DocumentAnalysis::create([
                    'document_id' => $document->id,
                    'organization_id' => $document->organization_id,
                    'requested_by' => $document->uploaded_by,
                    'analysis_type' => AnalysisType::FullSummary,
                    'llm_provider' => $analysisResult['llm_provider'],
                    'llm_model' => $modelSlugForRecord,
                    'content' => $analysisResult['content'],
                    'confidence_score' => match ($analysisResult['content']['nivel_confianza'] ?? null) {
                        'alto' => 0.85,
                        'medio' => 0.60,
                        'bajo' => 0.35,
                        default => null,
                    },
                    'input_tokens' => $analysisResult['input_tokens'],
                    'output_tokens' => $analysisResult['output_tokens'],
                    'processing_time_ms' => $analysisResult['processing_time_ms'],
                    'prompt_metadata' => $analysisResult['prompt_metadata'],
                    'status' => DocumentStatus::Processed,
                    'version' => 1,
                ]);
            } catch (\Throwable $llmException) {
                Log::warning('Automatic document analysis failed, but document was parsed successfully.', [
                    'document_id' => $document->id,
                    'provider' => $provider,
                    'model' => $modelSlugForRecord,
                    'error' => $llmException->getMessage(),
                ]);
                // Create failed analysis entry to let the user retry manually
                DocumentAnalysis::create([
                    'document_id' => $document->id,
                    'organization_id' => $document->organization_id,
                    'requested_by' => $document->uploaded_by,
                    'analysis_type' => AnalysisType::FullSummary,
                    'llm_provider' => $provider,
                    'llm_model' => $modelSlugForRecord,
                    'content' => ['error' => $llmException->getMessage()],
                    'status' => DocumentStatus::Failed,
                    'version' => 1,
                ]);
            }

            $document->transitionTo(DocumentStatus::Processed);
            $document->update(['processed_at' => now()]);

            Log::info('Document processing completed.', [
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Document processing failed.', [
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'error' => $exception->getMessage(),
            ]);

            // Transition to failed only if still in a valid state
            if ($document->canTransitionTo(DocumentStatus::Failed)) {
                $document->transitionTo(DocumentStatus::Failed);
                $document->update([
                    'metadata' => array_merge(
                        $document->metadata ?? [],
                        ['last_error' => $exception->getMessage()],
                    ),
                ]);
            }

            throw $exception; // Re-throw to trigger queue retry mechanism
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::critical('Document processing permanently failed.', [
            'document_id' => $this->document->id,
            'organization_id' => $this->document->organization_id,
            'error' => $exception?->getMessage(),
        ]);

        if ($this->document->canTransitionTo(DocumentStatus::Failed)) {
            $this->document->update([
                'status' => DocumentStatus::Failed,
                'metadata' => array_merge(
                    $this->document->metadata ?? [],
                    [
                        'last_error' => $exception?->getMessage(),
                        'permanently_failed_at' => now()->toIso8601String(),
                    ],
                ),
            ]);
        }
    }
}
