<?php

declare(strict_types=1);

namespace App\Events\Documents;

use App\Models\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a document has been successfully uploaded and stored.
 *
 * Listeners can react to this event for:
 * - Audit logging
 * - Notifications
 * - Analytics
 * - Future integrations (indexing, etc.)
 */
class DocumentUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Document $document,
        public readonly int $uploadedBy,
        public readonly string $ipAddress,
    ) {}
}
