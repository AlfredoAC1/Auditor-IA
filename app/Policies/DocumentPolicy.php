<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Policy for document authorization.
 *
 * All authorization checks enforce tenant isolation by verifying
 * that the user belongs to the same organization as the document.
 */
class DocumentPolicy
{
    /**
     * Determine if the user can view a listing of documents.
     *
     * Any authenticated user with an organization can list their org's documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasOrganization();
    }

    /**
     * Determine if the user can view a specific document.
     *
     * Enforces tenant isolation: user must belong to the document's organization.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->hasOrganization()
            && $user->belongsToOrganization($document->organization_id);
    }

    /**
     * Determine if the user can create documents.
     *
     * Any authenticated user with an organization can upload documents.
     */
    public function create(User $user): bool
    {
        return $user->hasOrganization();
    }

    /**
     * Determine if the user can update the document.
     *
     * Enforces tenant isolation.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->hasOrganization()
            && $user->belongsToOrganization($document->organization_id);
    }

    /**
     * Determine if the user can delete the document.
     *
     * Enforces tenant isolation.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->hasOrganization()
            && $user->belongsToOrganization($document->organization_id);
    }
}
