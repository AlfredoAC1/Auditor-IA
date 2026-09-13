<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

final class OrganizationPolicy
{
    /**
     * Determine whether the user can view the list of organizations.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can view the organization.
     */
    public function view(User $actor, Organization $organization): bool
    {
        return $actor->isAdmin() || $actor->organization_id === $organization->id;
    }

    /**
     * Determine whether the user can toggle the organization active status.
     */
    public function toggleStatus(User $actor, Organization $organization): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can update organization settings.
     * Allowed for System Admin or Organization Owner/Admin.
     */
    public function update(User $actor, Organization $organization): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        return $actor->hasOrganization()
            && $actor->belongsToOrganization($organization->id)
            && $actor->canManageSettings();
    }

    /**
     * Determine whether the user can manage organization billing and subscription.
     * Restricted to Organization Owner or System Admin.
     */
    public function manageBilling(User $actor, Organization $organization): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        return $actor->hasOrganization()
            && $actor->belongsToOrganization($organization->id)
            && $actor->canManageBilling();
    }
}
