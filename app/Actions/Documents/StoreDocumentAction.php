<?php

declare(strict_types=1);

namespace App\Actions\Documents;

use App\DTOs\Documents\StoreDocumentDTO;
use App\Enums\DocumentStatus;
use App\Events\Documents\DocumentUploaded;
use App\Jobs\Documents\ProcessDocumentJob;
use App\Models\Document;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action responsible for orchestrating the document upload flow.
 *
 * Coordinates:
 * 1. File storage (via DocumentStorageService)
 * 2. Database record creation (within a transaction)
 * 3. Async processing dispatch
 * 4. Event emission
 *
 * This action is the single entry point for document creation,
 * ensuring all business rules are enforced consistently.
 */
final class StoreDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storageService,
    ) {}

    /**
     * Execute the document upload action.
     *
     * @throws \RuntimeException If file storage fails
     * @throws \Throwable If database transaction fails
     */
    public function execute(StoreDocumentDTO $dto, ?string $ipAddress = null): Document
    {
        // 1. Store file on disk
        $storageResult = $this->storageService->store($dto->file, $dto->organizationId);

        try {
            // 2. Create database record within a transaction
            $document = DB::transaction(function () use ($dto, $storageResult): Document {
                return Document::create([
                    'organization_id' => $dto->organizationId,
                    'uploaded_by' => $dto->uploadedBy,
                    'original_filename' => $dto->file->getClientOriginalName(),
                    'stored_filename' => $storageResult['stored_filename'],
                    'storage_path' => $storageResult['storage_path'],
                    'disk' => $storageResult['disk'],
                    'mime_type' => $storageResult['mime_type'],
                    'file_size_bytes' => $storageResult['file_size_bytes'],
                    'file_hash' => $storageResult['file_hash'],
                    'status' => DocumentStatus::Pending,
                    'document_type' => $dto->documentType,
                    'metadata' => $dto->metadata,
                ]);
            });
        } catch (\Throwable $exception) {
            // Rollback: delete the stored file if DB insert fails
            $this->storageService->delete(
                $storageResult['storage_path'],
                $storageResult['stored_filename'],
                $storageResult['disk'],
            );

            Log::error('Document creation failed, file cleaned up.', [
                'organization_id' => $dto->organizationId,
                'uploaded_by' => $dto->uploadedBy,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        // 3. Dispatch async processing job
        ProcessDocumentJob::dispatch($document);

        // 4. Fire event for listeners (audit log, notifications, etc.)
        DocumentUploaded::dispatch(
            $document,
            $dto->uploadedBy,
            $ipAddress ?? 'unknown',
        );

        Log::info('Document uploaded successfully.', [
            'document_id' => $document->id,
            'organization_id' => $dto->organizationId,
            'uploaded_by' => $dto->uploadedBy,
            'file_size_bytes' => $storageResult['file_size_bytes'],
        ]);

        return $document;
    }
}
