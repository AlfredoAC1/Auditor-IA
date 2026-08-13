<?php

declare(strict_types=1);

namespace App\Actions\Documents;

use App\Models\Document;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action responsible for deleting a document in cascade.
 *
 * Deletes:
 * 1. Physical file on storage disk (local, S3, etc.)
 * 2. Database record (DocumentAnalysis & DocumentChunks automatically CASCADE)
 */
final class DeleteDocumentAction
{
    public function __construct(
        private readonly DocumentStorageService $storageService,
    ) {}

    /**
     * Execute document cascade deletion.
     *
     * @throws \Throwable If database transaction fails
     */
    public function execute(Document $document): void
    {
        $documentId = $document->id;
        $organizationId = $document->organization_id;
        $filename = $document->original_filename;
        $storagePath = $document->storage_path;
        $storedFilename = $document->stored_filename;
        $disk = $document->disk;

        DB::transaction(function () use ($document, $storagePath, $storedFilename, $disk): void {
            // 1. Delete stored file from disk if details are present
            if ($storagePath && $storedFilename) {
                try {
                    $this->storageService->delete($storagePath, $storedFilename, $disk);
                } catch (\Throwable $fileException) {
                    Log::warning('File cleanup failed during document deletion, continuing DB purge.', [
                        'document_id' => $document->id,
                        'error' => $fileException->getMessage(),
                    ]);
                }
            }

            // 2. Cascade delete database records (Analyses & Chunks + Hard Delete Document)
            $document->analyses()->delete();
            $document->chunks()->delete();
            $document->forceDelete();
        });

        Log::info('Document deleted in cascade successfully.', [
            'document_id' => $documentId,
            'organization_id' => $organizationId,
            'filename' => $filename,
        ]);
    }
}
