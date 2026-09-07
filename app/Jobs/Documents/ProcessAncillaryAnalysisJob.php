<?php

declare(strict_types=1);

namespace App\Jobs\Documents;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Models\AiModel;
use App\Models\AnalysisSupportingDocument;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use App\Services\Billing\AiCreditService;
use App\Services\Documents\DocumentParsingService;
use App\Services\LLM\LlmAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job responsible for parsing the newly uploaded ancillary document
 * and executing the contextual cross-document LLM analysis.
 *
 * NOTE: Does NOT call Unstructured API for the primary document;
 * uses the primary document's stored parsed JSON output directly.
 */
class ProcessAncillaryAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public array $backoff;

    public function __construct(
        public readonly DocumentAnalysis $analysis,
        public readonly Document $ancillaryDocument,
        public readonly ?string $modelSlug = null,
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
        LlmAnalysisService $llmService,
        AiCreditService $creditService,
    ): void {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $analysis = $this->analysis->fresh(['document']) ?? $this->analysis;
        $ancillaryDocument = $this->ancillaryDocument->fresh() ?? $this->ancillaryDocument;
        $primaryDocument = $analysis->document;

        if ($primaryDocument === null) {
            Log::error('Primary document not found for analysis.', [
                'analysis_id' => $analysis->id,
            ]);

            return;
        }

        Log::info('Ancillary document processing started.', [
            'analysis_id' => $analysis->id,
            'primary_document_id' => $primaryDocument->id,
            'ancillary_document_id' => $ancillaryDocument->id,
            'organization_id' => $analysis->organization_id,
        ]);

        try {
            // 1. Parsing ONLY the ancillary document (Primary doc JSON is already stored)
            $ancillaryDocument->beginProcessing();

            $compactContent = $ancillaryDocument->metadata['parsed_compact_content'] ?? null;

            if ($compactContent === null || $compactContent === '') {
                $parseResult = $parsingService->parse($ancillaryDocument);
                $compactContent = $parsingService->toCompactString($parseResult);

                $ancillaryDocument->update([
                    'metadata' => array_merge($ancillaryDocument->metadata ?? [], [
                        'parsed_elements' => $parseResult['elements'],
                        'parsed_element_count' => $parseResult['element_count'],
                        'parsed_estimated_tokens' => $parseResult['estimated_tokens'],
                        'parsed_compact_content' => $compactContent,
                        'parsed_at' => now()->toIso8601String(),
                    ]),
                    'status' => DocumentStatus::Processed,
                    'processed_at' => now(),
                ]);
            }

            // 2. Resolve AI Model (inherit from previous analysis and exclude Llama during testing)
            $modelSlug = $this->modelSlug ?? $ancillaryDocument->metadata['model_slug'] ?? null;
            $aiModel = null;

            if ($modelSlug !== null && is_string($modelSlug) && ! str_contains(strtolower($modelSlug), 'llama')) {
                $aiModel = AiModel::query()->where('slug', $modelSlug)->where('is_active', true)->first();
            }

            if ($aiModel === null && ! empty($analysis->llm_model) && ! str_contains(strtolower($analysis->llm_model), 'llama')) {
                $aiModel = AiModel::query()
                    ->where('slug', $analysis->llm_model)
                    ->where('is_active', true)
                    ->first()
                    ?? AiModel::query()
                        ->where('provider_model_id', $analysis->llm_model)
                        ->where('is_active', true)
                        ->first();
            }

            if ($aiModel === null && ! empty($analysis->llm_provider) && ! str_contains(strtolower($analysis->llm_provider), 'llama')) {
                $aiModel = AiModel::query()
                    ->where('provider', $analysis->llm_provider)
                    ->where('is_active', true)
                    ->ordered()
                    ->first();
            }

            if ($aiModel === null) {
                $primaryMeta = $primaryDocument->metadata ?? [];
                $docModel = $primaryMeta['model_slug'] ?? null;
                $docProv = $primaryMeta['llm_provider'] ?? null;

                if ($docModel && ! str_contains(strtolower($docModel), 'llama')) {
                    $aiModel = AiModel::query()->where('slug', $docModel)->where('is_active', true)->first();
                }
                if ($aiModel === null && $docProv && ! str_contains(strtolower($docProv), 'llama')) {
                    $aiModel = AiModel::query()->where('provider', $docProv)->where('is_active', true)->ordered()->first();
                }
            }

            if ($aiModel === null) {
                $aiModel = AiModel::query()
                    ->where('provider', 'cerebras')
                    ->where('is_active', true)
                    ->first()
                    ?? AiModel::defaultModel();
            }

            $modelIdentifier = "{$aiModel->provider}::{$aiModel->provider_model_id}";

            // 3. Perform Contextual LLM Analysis using existing primary JSON output + ancillary parsed content
            $result = $llmService->analyzeWithContext(
                primaryDocument: $primaryDocument,
                existingAnalysis: $analysis,
                supportingDocuments: new Collection([$ancillaryDocument]),
                analysisType: AnalysisType::ContextualSummary,
                overrideModel: $modelIdentifier,
            );

            $latestVersion = DocumentAnalysis::query()
                ->where('document_id', $primaryDocument->id)
                ->where('analysis_type', AnalysisType::ContextualSummary)
                ->max('version') ?? 0;

            // 4. Create new Contextual DocumentAnalysis record
            $newAnalysis = DocumentAnalysis::create([
                'document_id' => $primaryDocument->id,
                'organization_id' => $primaryDocument->organization_id,
                'requested_by' => $ancillaryDocument->uploaded_by,
                'analysis_type' => AnalysisType::ContextualSummary,
                'llm_provider' => $result['llm_provider'],
                'llm_model' => $aiModel->slug,
                'content' => $result['content'],
                'confidence_score' => match ($result['content']['nivel_confianza'] ?? null) {
                    'alto' => 0.85,
                    'medio' => 0.60,
                    'bajo' => 0.35,
                    default => null,
                },
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'processing_time_ms' => $result['processing_time_ms'],
                'prompt_metadata' => array_merge($result['prompt_metadata'] ?? [], [
                    'model_tier' => $aiModel->tier->value,
                    'credit_cost' => $aiModel->creditCostPerAnalysis(),
                    'prior_analysis_id' => $analysis->id,
                    'ancillary_document_id' => $ancillaryDocument->id,
                    'is_contextual_ancillary' => true,
                ]),
                'status' => DocumentStatus::Processed,
                'version' => $latestVersion + 1,
            ]);

            // Link ancillary document to the new analysis as well
            AnalysisSupportingDocument::firstOrCreate(
                [
                    'analysis_id' => $newAnalysis->id,
                    'document_id' => $ancillaryDocument->id,
                ],
                [
                    'organization_id' => $primaryDocument->organization_id,
                    'attached_by' => $ancillaryDocument->uploaded_by,
                    'relationship_type' => $ancillaryDocument->metadata['relationship_type'] ?? 'complementary',
                    'notes' => $ancillaryDocument->metadata['notes'] ?? null,
                ]
            );

            // 5. Deduct credits
            /** @var Organization $organization */
            $organization = $primaryDocument->organization;
            /** @var User $user */
            $user = $ancillaryDocument->uploader ?? $analysis->requester;

            if ($organization !== null && $user !== null) {
                $creditService->deductCredits($organization, $user, $aiModel, $newAnalysis);
            }

            // Save contextual analysis reference in ancillary document metadata
            $ancillaryDocument->update([
                'metadata' => array_merge($ancillaryDocument->metadata ?? [], [
                    'contextual_analysis_id' => $newAnalysis->id,
                ]),
            ]);

            Log::info('Ancillary document processing and contextual analysis completed successfully.', [
                'primary_document_id' => $primaryDocument->id,
                'ancillary_document_id' => $ancillaryDocument->id,
                'contextual_analysis_id' => $newAnalysis->id,
                'version' => $newAnalysis->version,
            ]);
        } catch (\Throwable $exception) {
            Log::error('ProcessAncillaryAnalysisJob failed.', [
                'analysis_id' => $analysis->id,
                'ancillary_document_id' => $ancillaryDocument->id,
                'error' => $exception->getMessage(),
            ]);

            if ($ancillaryDocument->canTransitionTo(DocumentStatus::Failed)) {
                $ancillaryDocument->transitionTo(DocumentStatus::Failed);
                $ancillaryDocument->update([
                    'metadata' => array_merge($ancillaryDocument->metadata ?? [], [
                        'last_error' => $exception->getMessage(),
                    ]),
                ]);
            }

            throw $exception;
        }
    }

    /**
     * Handle job permanent failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::critical('Ancillary document processing permanently failed.', [
            'analysis_id' => $this->analysis->id,
            'ancillary_document_id' => $this->ancillaryDocument->id,
            'error' => $exception?->getMessage(),
        ]);

        if ($this->ancillaryDocument->canTransitionTo(DocumentStatus::Failed)) {
            $this->ancillaryDocument->update([
                'status' => DocumentStatus::Failed,
                'metadata' => array_merge($this->ancillaryDocument->metadata ?? [], [
                    'last_error' => $exception?->getMessage(),
                    'permanently_failed_at' => now()->toIso8601String(),
                ]),
            ]);
        }
    }
}
