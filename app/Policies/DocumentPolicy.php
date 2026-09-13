<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Policy for document authorization.
 *
 * All authorization checks enforce tenant isolation and role permissions:
 * - Owner & Admin: full access within their organization.
 * - Auditor: read-only access (can view/audit, cannot upload, mutate, or delete).
 * - User: can upload, view org documents, update/delete own uploaded documents.
 */
class DocumentPolicy
{
    /**
     * Determine if the user can view a listing of documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasOrganization();
    }

    /**
     * Determine if the user can view a specific document.
     * Enforces tenant isolation: user must belong to the document's organization.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->hasOrganization()
            && $user->belongsToOrganization($document->organization_id);
    }

    /**
     * Determine if the user can create documents.
     * Auditors cannot upload documents (read-only audit role).
     */
    public function create(User $user): bool
    {
        return $user->hasOrganization() && $user->canMutateDocuments();
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        if (! $user->hasOrganization() || ! $user->belongsToOrganization($document->organization_id)) {
            return false;
        }

        if ($user->isOwner() || $user->isAdmin()) {
            return true;
        }

        return $user->isUser() && $document->uploaded_by === $user->id;
    }

    /**
     * Determine if the user can delete the document.
     *
     * - Owner & Admin can delete any document within their tenant.
     * - Regular User can only delete documents they uploaded themselves.
     * - Auditor cannot delete documents under any circumstance (audit trail protection).
     */
    public function delete(User $user, Document $document): bool
    {
        if (! $user->hasOrganization() || ! $user->belongsToOrganization($document->organization_id)) {
            return false;
        }

        if ($user->isOwner() || $user->isAdmin()) {
            return true;
        }

        return $user->isUser() && $document->uploaded_by === $user->id;
    }
}
