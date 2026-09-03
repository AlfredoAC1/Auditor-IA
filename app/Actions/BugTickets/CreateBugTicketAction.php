<?php

declare(strict_types=1);

namespace App\Actions\BugTickets;

use App\DTOs\BugTickets\CreateBugTicketDTO;
use App\Enums\BugTicketStatus;
use App\Models\BugTicket;
use Illuminate\Support\Facades\Log;

final class CreateBugTicketAction
{
    /**
     * Create a new bug ticket submitted by a user.
     */
    public function execute(CreateBugTicketDTO $dto): BugTicket
    {
        $ticket = BugTicket::create([
            'user_id' => $dto->userId,
            'organization_id' => $dto->organizationId,
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => BugTicketStatus::New,
        ]);

        Log::info('Bug ticket created.', [
            'ticket_id' => $ticket->id,
            'user_id' => $dto->userId,
            'organization_id' => $dto->organizationId,
            'title' => $dto->title,
        ]);

        return $ticket;
    }
}
