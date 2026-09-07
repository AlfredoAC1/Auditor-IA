<?php

declare(strict_types=1);

namespace App\Actions\Documents;

use App\DTOs\Documents\UploadAncillaryDocumentDTO;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Jobs\Documents\ProcessAncillaryAnalysisJob;
use App\Models\AiModel;
use App\Models\AnalysisSupportingDocument;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Services\Billing\AiCreditService;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action responsible for storing an ancillary document and dispatching
 * its asynchronous contextual parsing and analysis pipeline.
 */
final class StoreAndDispatchAncillaryAction
{
    public function __construct(
        private readonly DocumentStorageService $storageService,
        private readonly AiCreditService $creditService,
    ) {}

    /**
     * Store the uploaded ancillary document and queue contextual analysis.
     *
     * @return array{ancillary_document: Document, analysis: DocumentAnalysis}
     *
     * @throws \InvalidArgumentException If tenant isolation check fails
     * @throws \RuntimeException If storage or credit check fails
     */
    public function execute(
        UploadAncillaryDocumentDTO $dto,
        DocumentAnalysis $analysis,
    ): array {
        // 1. Tenant verification
        if ($analysis->organization_id !== $dto->organizationId) {
            throw new \InvalidArgumentException(
                "Analysis [{$analysis->id}] does not belong to organization [{$dto->organizationId}]."
            );
        }

        $organization = Organization::findOrFail($dto->organizationId);

        // 2. Resolve AI model & Credit check
        // Inherit provider and model from previous analysis, ensuring Llama is not used during testing.
        $aiModel = null;
        if ($dto->modelSlug !== null && $dto->modelSlug !== '' && ! str_contains(strtolower($dto->modelSlug), 'llama')) {
            $aiModel = AiModel::query()
                ->where('slug', $dto->modelSlug)
                ->where('is_active', true)
                ->first();
        }

        if ($aiModel === null) {
            // First check the previous analysis model
            if (! empty($analysis->llm_model) && ! str_contains(strtolower($analysis->llm_model), 'llama')) {
                $aiModel = AiModel::query()
                    ->where('slug', $analysis->llm_model)
                    ->where('is_active', true)
                    ->first()
                    ?? AiModel::query()
                    ->where('provider_model_id', $analysis->llm_model)
                    ->where('is_active', true)
                    ->first();
            }

            // Second check the previous analysis provider (e.g. 'cerebras')
            if ($aiModel === null && ! empty($analysis->llm_provider) && ! str_contains(strtolower($analysis->llm_provider), 'llama')) {
                $aiModel = AiModel::query()
                    ->where('provider', $analysis->llm_provider)
                    ->where('is_active', true)
                    ->ordered()
                    ->first();
            }

            // Third check primary document metadata
            if ($aiModel === null) {
                $primaryDoc = $analysis->document;
                $docModel = $primaryDoc?->metadata['model_slug'] ?? null;
                $docProvider = $primaryDoc?->metadata['llm_provider'] ?? null;

                if ($docModel && ! str_contains(strtolower($docModel), 'llama')) {
                    $aiModel = AiModel::query()->where('slug', $docModel)->where('is_active', true)->first();
                }
                if ($aiModel === null && $docProvider && ! str_contains(strtolower($docProvider), 'llama')) {
                    $aiModel = AiModel::query()->where('provider', $docProvider)->where('is_active', true)->ordered()->first();
                }
            }

            // Fallback to Cerebras or default non-llama active model
            if ($aiModel === null) {
                $aiModel = AiModel::query()
                    ->where('provider', 'cerebras')
                    ->where('is_active', true)
                    ->first()
                    ?? AiModel::defaultModel();
            }
        }

        if (! $this->creditService->canPerformAnalysis($organization, $aiModel)) {
            throw new \RuntimeException(
                'Créditos de Inteligencia Artificial insuficientes para realizar el análisis contextual.'
            );
        }

        // 3. Store the physical file on private disk
        $storageResult = $this->storageService->store($dto->file, $dto->organizationId);

        try {
            // 4. Create Document and Pivot record within transaction
            [$ancillaryDocument, $pivot] = DB::transaction(function () use ($dto, $analysis, $storageResult, $aiModel): array {
                $doc = Document::create([
                    'organization_id'   => $dto->organizationId,
                    'uploaded_by'       => $dto->userId,
                    'original_filename' => $dto->file->getClientOriginalName(),
                    'stored_filename'   => $storageResult['stored_filename'],
                    'storage_path'      => $storageResult['storage_path'],
                    'disk'              => $storageResult['disk'],
                    'mime_type'         => $storageResult['mime_type'],
                    'file_size_bytes'   => $storageResult['file_size_bytes'],
                    'file_hash'         => $storageResult['file_hash'],
                    'status'            => DocumentStatus::Pending,
                    'document_type'     => DocumentType::Amendment,
                    'metadata'          => [
                        'is_ancillary'        => true,
                        'primary_document_id' => $analysis->document_id,
                        'primary_analysis_id' => $analysis->id,
                        'relationship_type'   => $dto->relationshipType,
                        'notes'               => $dto->notes,
                        'model_slug'          => $aiModel->slug,
                        'llm_provider'        => $aiModel->provider,
                    ],
                ]);

                $piv = AnalysisSupportingDocument::create([
                    'analysis_id'       => $analysis->id,
                    'document_id'       => $doc->id,
                    'organization_id'   => $dto->organizationId,
                    'attached_by'       => $dto->userId,
                    'relationship_type' => $dto->relationshipType,
                    'notes'             => $dto->notes,
                ]);

                return [$doc, $piv];
            });
        } catch (\Throwable $exception) {
            // Clean up stored file if DB insert fails
            $this->storageService->delete(
                $storageResult['storage_path'],
                $storageResult['stored_filename'],
                $storageResult['disk'],
            );

            Log::error('Ancillary document record creation failed, file cleaned up.', [
                'organization_id' => $dto->organizationId,
                'analysis_id'     => $analysis->id,
                'error'           => $exception->getMessage(),
            ]);

            throw $exception;
        }

        // 5. Dispatch async processing job (immediate after HTTP response in web, queue in tests/background)
        if (! app()->runningUnitTests()) {
            ProcessAncillaryAnalysisJob::dispatchAfterResponse(
                analysis: $analysis,
                ancillaryDocument: $ancillaryDocument,
                modelSlug: $aiModel->slug,
            );
        } else {
            ProcessAncillaryAnalysisJob::dispatch(
                analysis: $analysis,
                ancillaryDocument: $ancillaryDocument,
                modelSlug: $aiModel->slug,
            )->onQueue(config('documents.processing_queue', 'documents'));
        }

        Log::info('Ancillary document uploaded and contextual job queued.', [
            'analysis_id'           => $analysis->id,
            'primary_document_id'   => $analysis->document_id,
            'ancillary_document_id' => $ancillaryDocument->id,
            'organization_id'       => $dto->organizationId,
            'model_slug'            => $aiModel->slug,
        ]);

        return [
            'ancillary_document' => $ancillaryDocument,
            'analysis'           => $analysis,
        ];
    }
}
