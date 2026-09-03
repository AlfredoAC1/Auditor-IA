<?php

declare(strict_types=1);

namespace App\Actions\BugTickets;

use App\Enums\BugTicketStatus;
use App\Models\BugTicket;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class ResolveBugTicketAction
{
    /**
     * Mark a bug ticket as resolved / dismissed by an administrator.
     */
    public function execute(BugTicket $ticket, User $adminUser): BugTicket
    {
        $ticket->status = BugTicketStatus::Resolved;
        $ticket->resolved_at = now();
        $ticket->resolved_by = $adminUser->id;
        $ticket->save();

        Log::info('Bug ticket resolved.', [
            'ticket_id' => $ticket->id,
            'resolved_by' => $adminUser->id,
            'resolved_at' => $ticket->resolved_at->toIso8601String(),
        ]);

        return $ticket;
    }
}
