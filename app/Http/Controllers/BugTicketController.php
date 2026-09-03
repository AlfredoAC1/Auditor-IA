<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BugTickets\CreateBugTicketAction;
use App\DTOs\BugTickets\CreateBugTicketDTO;
use App\Http\Requests\BugTickets\CreateBugTicketRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class BugTicketController extends Controller
{
    public function __construct(
        private readonly CreateBugTicketAction $createTicketAction,
    ) {}

    /**
     * Store a new bug ticket submitted by a user.
     */
    public function store(CreateBugTicketRequest $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $dto = CreateBugTicketDTO::fromRequest($request, $user);
        $ticket = $this->createTicketAction->execute($dto);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Reporte de error enviado correctamente.',
                'ticket_id' => $ticket->id,
            ], 201);
        }

        return back()->with('success', 'Reporte de error enviado exitosamente. Gracias por tu reporte.');
    }
}
