<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Documents\DeleteDocumentAction;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneExpiredFreeDocumentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:prune-free {--days=7 : Retraction days threshold for Free users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically purge documents and associated analyses older than 7 days for Free plan users.';

    /**
     * Execute the console command.
     */
    public function handle(DeleteDocumentAction $deleteAction): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Searching for Free user documents created on or before [{$cutoffDate->toDateTimeString()}]...");

        // Query candidate documents older than specified cutoff date
        $candidateDocuments = Document::query()
            ->with('organization.subscriptions.plan')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        $deletedCount = 0;
        $freedBytes = 0;

        foreach ($candidateDocuments as $document) {
            /** @var Document $document */
            if (! $document->organization || $document->organization->hasProAccess()) {
                // Skip documents belonging to Pro or Lifetime subscriptions
                continue;
            }

            try {
                $fileSize = $document->file_size_bytes;
                $deleteAction->execute($document);
                $deletedCount++;
                $freedBytes += $fileSize;
            } catch (\Throwable $exception) {
                Log::error('Failed to prune expired free document.', [
                    'document_id' => $document->id,
                    'error' => $exception->getMessage(),
                ]);
                $this->error("Failed to delete document [{$document->id}]: {$exception->getMessage()}");
            }
        }

        $formattedMb = number_format($freedBytes / (1024 * 1024), 2);
        $this->info("Pruning complete. Deleted {$deletedCount} document(s) freeing {$formattedMb} MB of storage.");

        Log::info('Automated free document pruning completed.', [
            'pruned_count' => $deletedCount,
            'freed_bytes' => $freedBytes,
            'days_threshold' => $days,
        ]);

        return Command::SUCCESS;
    }
}
