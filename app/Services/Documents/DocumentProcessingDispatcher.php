<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Jobs\Documents\ProcessDocumentJob;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches document processing jobs with optional immediate execution.
 *
 * Dashboard uploads use after-response dispatch so processing begins
 * in the same PHP request without requiring a separate queue worker.
 */
final class DocumentProcessingDispatcher
{
    public function dispatch(Document $document, bool $processImmediately = false): void
    {
        if ($processImmediately && ! app()->runningUnitTests()) {
            ProcessDocumentJob::dispatchAfterResponse($document);

            Log::info('Document processing scheduled after HTTP response.', [
                'document_id' => $document->id,
            ]);

            return;
        }

        if (app()->runningUnitTests() && config('queue.default') === 'sync') {
            ProcessDocumentJob::dispatchSync($document);

            return;
        }

        ProcessDocumentJob::dispatch($document);
    }
}
