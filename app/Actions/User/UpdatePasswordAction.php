<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdatePasswordAction
{
    /**
     * Verify the current password, persist the new one, and revoke all active tokens/sessions.
     *
     * @throws ValidationException
     */
    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ])->save();

        // Invalidate all active Sanctum API tokens immediately
        $revokedTokensCount = $user->tokens()->delete();

        // If authenticated in a web session, invalidate concurrent sessions
        if (Auth::check() && Auth::id() === $user->id) {
            try {
                Auth::logoutOtherDevices($newPassword);
            } catch (\Throwable $e) {
                // Session driver may not support logoutOtherDevices if not using database session
            }
        }

        Log::notice('User password changed and all active tokens revoked.', [
            'user_id' => $user->id,
            'revoked_tokens' => $revokedTokensCount,
        ]);
    }
}
