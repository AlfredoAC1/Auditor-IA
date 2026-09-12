<?php

declare(strict_types=1);

namespace App\DTOs\Documents;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Data Transfer Object for attaching a supporting document to an existing analysis.
 *
 * Encapsulates all validated data needed to create an AnalysisSupportingDocument
 * record, decoupling the HTTP layer from business logic.
 */
final readonly class AttachSupportingDocumentDTO
{
    public function __construct(
        /** UUID of the DocumentAnalysis that owns this context */
        public string $analysisId,
        /** UUID of the Document being attached as supporting context */
        public string $documentId,
        /** Tenant identifier — used for isolation enforcement */
        public int $organizationId,
        /** ID of the user performing the attachment */
        public int $attachedBy,
        /** Semantic relationship type: referenced|related|clarification|complementary */
        public ?string $relationshipType = null,
        /** Optional human-readable note explaining the relevance */
        public ?string $notes = null,
    ) {}

    /**
     * Create a DTO from a validated Form Request.
     *
     * @param  string  $analysisId  UUID of the parent analysis
     */
    public static function fromRequest(
        Request $request,
        string $analysisId,
    ): self {
        /** @var User $user */
        $user = $request->user();

        return new self(
            analysisId: $analysisId,
            documentId: (string) $request->input('document_id'),
            organizationId: (int) $user->organization_id,
            attachedBy: (int) $user->id,
            relationshipType: $request->filled('relationship_type')
                ? (string) $request->input('relationship_type')
                : null,
            notes: $request->filled('notes')
                ? (string) $request->input('notes')
                : null,
        );
    }
}
