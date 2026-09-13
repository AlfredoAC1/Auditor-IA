<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    /**
     * Determine whether the user can view the list of users.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin() || $actor->isOwner();
    }

    /**
     * Determine whether the user can view the specific user.
     */
    public function view(User $actor, User $target): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        if ($actor->id === $target->id) {
            return true;
        }

        return $actor->isOwner()
            && $actor->hasOrganization()
            && $actor->belongsToOrganization($target->organization_id);
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $actor): bool
    {
        return $actor->isAdmin() || $actor->isOwner();
    }

    /**
     * Determine whether the user can update the specific user.
     */
    public function update(User $actor, User $target): bool
    {
        if ($actor->isAdmin()) {
            return true;
        }

        // Self-profile update is allowed (profile details only)
        if ($actor->id === $target->id) {
            return true;
        }

        // Owner can update users within their own organization, but cannot demote themselves here
        return $actor->isOwner()
            && $actor->hasOrganization()
            && $actor->belongsToOrganization($target->organization_id);
    }

    /**
     * Determine whether the user can delete the specific user.
     * Prevents self-deletion and non-admin/owner deletion.
     */
    public function delete(User $actor, User $target): bool
    {
        // Prevent self-deletion via admin/management operations
        if ($actor->id === $target->id) {
            return false;
        }

        if ($actor->isAdmin()) {
            return true;
        }

        // Owner can delete non-owner members of their organization
        if ($actor->isOwner() && $actor->hasOrganization() && $actor->belongsToOrganization($target->organization_id)) {
            return ! $target->isOwner();
        }

        return false;
    }
}
