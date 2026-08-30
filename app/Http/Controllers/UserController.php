<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\User\DeleteAccountAction;
use App\Actions\User\DismissTutorialAction;
use App\Actions\User\UpdateAvatarAction;
use App\Actions\User\UpdatePasswordAction;
use App\Actions\User\UpdateProfileAction;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\DismissTutorialRequest;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function __construct(
        private readonly DismissTutorialAction $dismissAction,
        private readonly UpdateProfileAction   $updateProfileAction,
        private readonly UpdateAvatarAction    $updateAvatarAction,
        private readonly UpdatePasswordAction  $updatePasswordAction,
        private readonly DeleteAccountAction   $deleteAccountAction,
    ) {}

    // ---------------------------------------------------------------
    // Settings page
    // ---------------------------------------------------------------

    /**
     * Show the account settings page.
     */
    public function showSettings(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('settings', compact('user'));
    }

    // ---------------------------------------------------------------
    // Profile
    // ---------------------------------------------------------------

    /**
     * Update the authenticated user's display name.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->updateProfileAction->execute($user, $request->validated()['name']);

        return redirect()
            ->route('settings')
            ->with('success', 'Nombre actualizado correctamente.');
    }

    // ---------------------------------------------------------------
    // Avatar
    // ---------------------------------------------------------------

    /**
     * Upload and store a new profile picture.
     */
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->updateAvatarAction->execute($user, $request->file('avatar'));
        } catch (\Throwable $e) {
            Log::error('Avatar upload failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return redirect()
                ->route('settings')
                ->with('error', 'No se pudo guardar la imagen. Inténtalo de nuevo.');
        }

        return redirect()
            ->route('settings')
            ->with('success', 'Foto de perfil actualizada.');
    }

    /**
     * Serve the authenticated user's private avatar via a signed route.
     */
    public function serveAvatar(User $user): Response
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        // Only allow a user to fetch their own avatar
        if ($authUser->id !== $user->id) {
            abort(403);
        }

        if ($user->avatar_path === null || ! Storage::disk('local')->exists($user->avatar_path)) {
            abort(404);
        }

        $file     = Storage::disk('local')->get($user->avatar_path);
        $mimeType = Storage::disk('local')->mimeType($user->avatar_path) ?: 'image/jpeg';

        return response($file, 200)->header('Content-Type', $mimeType);
    }

    // ---------------------------------------------------------------
    // Password
    // ---------------------------------------------------------------

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $this->updatePasswordAction->execute(
                $user,
                $request->validated()['current_password'],
                $request->validated()['password'],
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('settings', ['tab' => 'security'])
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('settings', ['tab' => 'security'])
            ->with('success', 'Contraseña actualizada correctamente.');
    }

    // ---------------------------------------------------------------
    // Account deletion
    // ---------------------------------------------------------------

    /**
     * Permanently delete the account, organization, and all associated data.
     */
    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        try {
            $this->deleteAccountAction->execute($user);
        } catch (\Throwable $e) {
            Log::error('Account deletion failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return redirect()
                ->route('login')
                ->with('error', 'Ocurrió un error al eliminar la cuenta. Contacta soporte.');
        }

        return redirect()
            ->route('landing')
            ->with('success', 'Tu cuenta ha sido eliminada permanentemente.');
    }

    // ---------------------------------------------------------------
    // Tutorial
    // ---------------------------------------------------------------

    /**
     * Dismiss the onboarding tutorial for the authenticated user.
     */
    public function dismissTutorial(DismissTutorialRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado.'], 401);
        }

        $this->dismissAction->execute($user);

        return response()->json(['success' => true, 'message' => 'Tutorial descartado correctamente.']);
    }
}
