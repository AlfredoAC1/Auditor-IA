<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateUserAction;
use App\Actions\Admin\DeleteUserAction;
use App\Actions\Admin\UpdateUserAction;
use App\DTOs\Admin\CreateUserDTO;
use App\DTOs\Admin\UpdateUserDTO;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCreateUserRequest;
use App\Http\Requests\Admin\AdminUpdateUserRequest;
use App\Models\Organization;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $userService,
        private readonly CreateUserAction $createUserAction,
        private readonly UpdateUserAction $updateUserAction,
        private readonly DeleteUserAction $deleteUserAction,
    ) {}

    /**
     * Display a listing of users with filters and aggregations.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString() ?: null;
        $role = $request->string('role')->toString() ?: null;
        $organizationId = $request->filled('organization_id') ? (int) $request->input('organization_id') : null;

        $users = $this->userService->getPaginatedUsers($search, $role, $organizationId);
        $organizations = Organization::query()->orderBy('name')->get(['id', 'name']);
        $roles = UserRole::cases();

        return view('admin.users.index', [
            'users' => $users,
            'organizations' => $organizations,
            'roles' => $roles,
            'search' => $search,
            'selectedRole' => $role,
            'selectedOrg' => $organizationId,
        ]);
    }

    /**
     * Show the form for creating a new user or administrator.
     */
    public function create(): View
    {
        $organizations = Organization::query()->orderBy('name')->get(['id', 'name']);
        $roles = UserRole::cases();

        return view('admin.users.create', [
            'organizations' => $organizations,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user/administrator.
     */
    public function store(AdminCreateUserRequest $request): RedirectResponse
    {
        $dto = CreateUserDTO::fromRequest($request);

        /** @var User $actor */
        $actor = $request->user();

        $user = $this->createUserAction->execute($dto, $actor);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Usuario '{$user->name}' ({$user->role->label()}) creado exitosamente.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $organizations = Organization::query()->orderBy('name')->get(['id', 'name']);
        $roles = UserRole::cases();

        return view('admin.users.edit', [
            'user' => $user,
            'organizations' => $organizations,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(AdminUpdateUserRequest $request, User $user): RedirectResponse
    {
        $dto = UpdateUserDTO::fromRequest($request);

        /** @var User $actor */
        $actor = $request->user();

        $this->updateUserAction->execute($user, $dto, $actor);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Usuario '{$user->name}' actualizado correctamente.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        $userName = $user->name;
        $this->deleteUserAction->execute($user, $actor);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Usuario '{$userName}' eliminado exitosamente.");
    }
}
