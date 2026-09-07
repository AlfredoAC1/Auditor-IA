<?php

declare(strict_types=1);

namespace App\DTOs\Documents;

use App\Enums\DocumentType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Data Transfer Object for document upload operations.
 *
 * Encapsulates all validated data needed to store a document,
 * decoupling the HTTP layer from business logic.
 */
final readonly class StoreDocumentDTO
{
    public function __construct(
        public UploadedFile $file,
        public int $organizationId,
        public int $uploadedBy,
        public ?DocumentType $documentType = null,
        public ?array $metadata = null,
    ) {}

    /**
     * Create a DTO from a validated Form Request.
     */
    public static function fromRequest(
        Request $request,
    ): self {
        /** @var User $user */
        $user = $request->user();

        return new self(
            file: $request->file('file'),
            organizationId: (int) $user->organization_id,
            uploadedBy: (int) $user->id,
            documentType: $request->filled('document_type')
                ? DocumentType::tryFrom($request->input('document_type'))
                : null,
            metadata: $request->input('metadata'),
        );
    }
}
