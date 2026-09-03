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
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can view the specific user.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->isAdmin() || $actor->id === $target->id;
    }

    /**
     * Determine whether the user can create users through admin controls.
     */
    public function create(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can update the specific user.
     */
    public function update(User $actor, User $target): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can delete the specific user.
     * Prevents self-deletion at policy level.
     */
    public function delete(User $actor, User $target): bool
    {
        return $actor->isAdmin() && $actor->id !== $target->id;
    }
}
