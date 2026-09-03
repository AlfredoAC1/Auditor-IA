<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\BugTickets\ResolveBugTicketAction;
use App\Enums\BugTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminBugTicketController extends Controller
{
    public function __construct(
        private readonly ResolveBugTicketAction $resolveAction,
    ) {}

    /**
     * Display a listing of bug tickets.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'new';
        $search = $request->string('search')->toString() ?: null;

        $tickets = BugTicket::query()
            ->with(['user:id,name,email', 'organization:id,name', 'resolver:id,name'])
            ->when($status !== 'all', function (Builder $query) use ($status): void {
                if ($status === 'new') {
                    $query->new();
                } elseif ($status === 'resolved') {
                    $query->resolved();
                }
            })
            ->when($search, function (Builder $query, string $term): void {
                $trimmed = trim($term);
                $query->where(function (Builder $q) use ($trimmed): void {
                    $q->where('title', 'like', "%{$trimmed}%")
                      ->orWhere('description', 'like', "%{$trimmed}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $newCount = BugTicket::query()->new()->count();
        $resolvedCount = BugTicket::query()->resolved()->count();

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'currentStatus' => $status,
            'search' => $search,
            'newCount' => $newCount,
            'resolvedCount' => $resolvedCount,
        ]);
    }

    /**
     * Resolve / dismiss the specified bug ticket.
     */
    public function resolve(Request $request, BugTicket $ticket): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $this->resolveAction->execute($ticket, $actor);

        return redirect()
            ->route('admin.tickets.index')
            ->with('success', "Ticket #{$ticket->id} marcado como Resuelto.");
    }
}
