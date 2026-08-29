<?php

declare(strict_types=1);

namespace App\Actions\Documents;

use App\DTOs\Documents\AttachSupportingDocumentDTO;
use App\Models\AnalysisSupportingDocument;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action responsible for attaching a supporting document to an existing analysis.
 *
 * Enforces:
 * - Tenant isolation: both analysis and supporting document must belong to the same organization
 * - Idempotency guard: prevents attaching the same document twice
 * - Prevents self-reference: the primary document cannot be attached to its own analysis
 * - Structured audit logging on every attachment
 */
final class AttachSupportingDocumentAction
{
    /**
     * Execute the attachment.
     *
     * @throws \InvalidArgumentException If tenant isolation check fails
     * @throws \LogicException If the document is already attached or is the primary document
     * @throws \Throwable If the database transaction fails
     */
    public function execute(
        AttachSupportingDocumentDTO $dto,
        DocumentAnalysis $analysis,
        Document $supportingDocument,
    ): AnalysisSupportingDocument {
        // 1. Tenant isolation — both must belong to the same organization
        if ($analysis->organization_id !== $dto->organizationId) {
            throw new \InvalidArgumentException(
                "Analysis [{$analysis->id}] does not belong to organization [{$dto->organizationId}]."
            );
        }

        if ($supportingDocument->organization_id !== $dto->organizationId) {
            throw new \InvalidArgumentException(
                "Supporting document [{$supportingDocument->id}] does not belong to organization [{$dto->organizationId}]."
            );
        }

        // 2. Prevent self-reference (supporting doc cannot be the analysis's primary doc)
        if ($supportingDocument->id === $analysis->document_id) {
            throw new \LogicException(
                "Document [{$supportingDocument->id}] is the primary document of analysis [{$analysis->id}] and cannot be attached as a supporting document."
            );
        }

        // 3. Idempotency — check if already attached
        $alreadyAttached = AnalysisSupportingDocument::query()
            ->where('analysis_id', $analysis->id)
            ->where('document_id', $supportingDocument->id)
            ->exists();

        if ($alreadyAttached) {
            throw new \LogicException(
                "Document [{$supportingDocument->id}] is already attached to analysis [{$analysis->id}]."
            );
        }

        // 4. Create pivot record within a transaction
        $pivot = DB::transaction(function () use ($dto, $analysis, $supportingDocument): AnalysisSupportingDocument {
            return AnalysisSupportingDocument::create([
                'analysis_id'       => $analysis->id,
                'document_id'       => $supportingDocument->id,
                'organization_id'   => $dto->organizationId,
                'attached_by'       => $dto->attachedBy,
                'relationship_type' => $dto->relationshipType,
                'notes'             => $dto->notes,
            ]);
        });

        Log::info('Supporting document attached to analysis.', [
            'analysis_id'           => $analysis->id,
            'document_id'           => $analysis->document_id,
            'supporting_document_id'=> $supportingDocument->id,
            'organization_id'       => $dto->organizationId,
            'attached_by'           => $dto->attachedBy,
            'relationship_type'     => $dto->relationshipType,
        ]);

        return $pivot;
    }
}
