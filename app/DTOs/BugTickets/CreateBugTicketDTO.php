<?php

declare(strict_types=1);

namespace App\DTOs\BugTickets;

use App\Http\Requests\BugTickets\CreateBugTicketRequest;
use App\Models\User;

final readonly class CreateBugTicketDTO
{
    public function __construct(
        public int $userId,
        public ?int $organizationId,
        public string $title,
        public string $description,
    ) {}

    public static function fromRequest(CreateBugTicketRequest $request, User $user): self
    {
        return new self(
            userId: $user->id,
            organizationId: $user->organization_id,
            title: trim((string) $request->validated('title')),
            description: trim((string) $request->validated('description')),
        );
    }
}
