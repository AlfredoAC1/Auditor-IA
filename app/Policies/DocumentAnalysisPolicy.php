<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentAnalysis;
use App\Models\User;

/**
 * Policy for document analysis authorization.
 *
 * Enforces tenant isolation for viewing/managing analyses,
 * and restricts advanced features (such as attaching ancillary documents)
 * to organizations with Pro-level access.
 */
class DocumentAnalysisPolicy
{
    /**
     * Determine if the user can view the analysis.
     */
    public function view(User $user, DocumentAnalysis $analysis): bool
    {
        return $user->hasOrganization()
            && $user->belongsToOrganization($analysis->organization_id);
    }

    /**
     * Determine if the user can create analyses.
     */
    public function create(User $user): bool
    {
        return $user->hasOrganization();
    }

    /**
     * Determine if the user can attach an ancillary document to this analysis.
     *
     * Requires:
     * 1. The user must belong to the organization that owns the analysis.
     * 2. The user's organization must have an active Pro subscription (or lifetime).
     */
    public function addAncillary(User $user, DocumentAnalysis $analysis): bool
    {
        if (! $user->hasOrganization()) {
            return false;
        }

        if (! $user->belongsToOrganization($analysis->organization_id)) {
            return false;
        }

        return $user->hasProAccess();
    }

    /**
     * Determine if the user can delete the analysis.
     *
     * Only Owner and Admin can delete analysis records to preserve
     * compliance and forensic audit history for Auditors and Users.
     */
    public function delete(User $user, DocumentAnalysis $analysis): bool
    {
        if (! $user->hasOrganization() || ! $user->belongsToOrganization($analysis->organization_id)) {
            return false;
        }

        return $user->isOwner() || $user->isAdmin();
    }
}
