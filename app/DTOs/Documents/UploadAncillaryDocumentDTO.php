<?php

declare(strict_types=1);

namespace App\DTOs\Documents;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Data Transfer Object for uploading and processing an ancillary document
 * directly into the context of an existing DocumentAnalysis.
 */
final readonly class UploadAncillaryDocumentDTO
{
    public function __construct(
        public UploadedFile $file,
        public string $analysisId,
        public string $primaryDocumentId,
        public int $organizationId,
        public int $userId,
        public ?string $relationshipType = null,
        public ?string $notes = null,
        public ?string $modelSlug = null,
    ) {}

    /**
     * Create a DTO from a validated Request and route parameters.
     */
    public static function fromRequest(
        Request $request,
        string $analysisId,
        string $primaryDocumentId,
    ): self {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        return new self(
            file: $file,
            analysisId: $analysisId,
            primaryDocumentId: $primaryDocumentId,
            organizationId: (int) $user->organization_id,
            userId: (int) $user->id,
            relationshipType: $request->filled('relationship_type')
                ? (string) $request->input('relationship_type')
                : null,
            notes: $request->filled('notes')
                ? (string) $request->input('notes')
                : null,
            modelSlug: $request->filled('model_slug')
                ? (string) $request->input('model_slug')
                : null,
        );
    }
}
