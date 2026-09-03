<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BugTicket;
use App\Models\User;

final class BugTicketPolicy
{
    /**
     * Determine whether the user can view the bug tickets list.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can view the bug ticket.
     */
    public function view(User $actor, BugTicket $ticket): bool
    {
        return $actor->isAdmin() || $actor->id === $ticket->user_id;
    }

    /**
     * Determine whether the user can create a bug ticket.
     */
    public function create(User $actor): bool
    {
        return true;
    }

    /**
     * Determine whether the user can resolve / dismiss the bug ticket.
     */
    public function resolve(User $actor, BugTicket $ticket): bool
    {
        return $actor->isAdmin();
    }

    /**
     * Determine whether the user can delete the bug ticket.
     */
    public function delete(User $actor, BugTicket $ticket): bool
    {
        return $actor->isAdmin();
    }
}
