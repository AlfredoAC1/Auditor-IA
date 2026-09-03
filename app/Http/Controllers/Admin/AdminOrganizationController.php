<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ToggleOrganizationStatusAction;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\Admin\AdminOrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminOrganizationController extends Controller
{
    public function __construct(
        private readonly AdminOrganizationService $organizationService,
        private readonly ToggleOrganizationStatusAction $toggleAction,
    ) {}

    /**
     * Display a listing of organizations.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString() ?: null;
        $organizations = $this->organizationService->getPaginatedOrganizations($search);

        return view('admin.organizations.index', [
            'organizations' => $organizations,
            'search' => $search,
        ]);
    }

    /**
     * Toggle the active status of an organization.
     */
    public function toggleStatus(Request $request, Organization $organization): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $this->toggleAction->execute($organization, $actor);

        $state = $organization->is_active ? 'activada' : 'desactivada';

        return redirect()
            ->route('admin.organizations.index')
            ->with('success', "Organización '{$organization->name}' {$state} correctamente.");
    }
}
