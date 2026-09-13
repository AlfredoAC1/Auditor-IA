<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PasswordResetService
{
    /**
     * Broker instance.
     */
    private function broker(): PasswordBroker
    {
        return Password::broker();
    }

    /**
     * Send a password reset link to the given email address.
     *
     * Prevents account enumeration by always logging and returning a generic status
     * regardless of whether the email exists in the database.
     */
    public function sendResetLink(string $email): string
    {
        $status = $this->broker()->sendResetLink(['email' => Str::lower($email)]);

        Log::info('Password reset link requested.', [
            'email' => Str::mask($email, '*', 3),
            'status' => $status,
        ]);

        return 'Si la cuenta existe, se ha enviado un enlace de restablecimiento de contraseña.';
    }

    /**
     * Reset the user's password using a cryptographically secure token.
     *
     * Security safeguards:
     * - Token is single-use and automatically purged upon consumption.
     * - Token expires within 60 minutes.
     * - All active Sanctum API tokens are revoked immediately.
     * - Remember token is rotated.
     * - Fires PasswordReset event.
     *
     * @throws ValidationException
     */
    public function reset(string $email, string $token, string $newPassword): User
    {
        /** @var User|null $updatedUser */
        $updatedUser = null;

        $status = $this->broker()->reset(
            [
                'email' => Str::lower($email),
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
                'token' => $token,
            ],
            function (User $user, string $password) use (&$updatedUser): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all active personal access tokens for defense-in-depth
                $revokedCount = $user->tokens()->delete();

                event(new PasswordReset($user));

                Log::notice('Password successfully reset via token and all active tokens revoked.', [
                    'user_id' => $user->id,
                    'revoked_tokens' => $revokedCount,
                ]);

                $updatedUser = $user;
            }
        );

        if ($status !== Password::PASSWORD_RESET || $updatedUser === null) {
            Log::warning('Password reset attempt failed.', [
                'email' => Str::mask($email, '*', 3),
                'status' => $status,
            ]);

            throw ValidationException::withMessages([
                'email' => ['El token de recuperación es inválido o ha expirado.'],
            ]);
        }

        return $updatedUser;
    }

    /**
     * Generate a one-time cryptographically secure password reset token directly for a user.
     * Useful for automated administrative resets or secure email flows.
     */
    public function createToken(User $user): string
    {
        return $this->broker()->createToken($user);
    }
}
