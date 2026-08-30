<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class UpdatePasswordAction
{
    /**
     * Verify the current password then hash and persist the new one.
     *
     * @throws ValidationException
     */
    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contrase�a actual no es correcta.'],
            ]);
        }

        $user->update(['password' => Hash::make($newPassword)]);

        Log::info('User password changed.', ['user_id' => $user->id]);
    }
}
