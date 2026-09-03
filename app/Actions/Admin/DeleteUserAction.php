<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class DeleteUserAction
{
    /**
     * Delete user safely preventing self-deletion and last admin deletion.
     */
    public function execute(User $targetUser, User $adminUser): void
    {
        // 1. Self-deletion guard
        if ($targetUser->id === $adminUser->id) {
            throw ValidationException::withMessages([
                'user' => 'No puedes eliminar tu propia cuenta de administrador.',
            ]);
        }

        // 2. Last admin deletion guard
        if ($targetUser->isAdmin()) {
            $otherAdminsCount = User::query()
                ->where('role', UserRole::Admin)
                ->where('id', '!=', $targetUser->id)
                ->count();

            if ($otherAdminsCount === 0) {
                throw ValidationException::withMessages([
                    'user' => 'No es posible eliminar al único administrador del sistema.',
                ]);
            }
        }

        DB::transaction(function () use ($targetUser, $adminUser): void {
            $targetUserId = $targetUser->id;
            $targetUserEmail = $targetUser->email;
            $targetUserRole = $targetUser->role?->value;

            $targetUser->delete();

            Log::info('Admin deleted user.', [
                'admin_id' => $adminUser->id,
                'deleted_user_id' => $targetUserId,
                'email' => $targetUserEmail,
                'role' => $targetUserRole,
            ]);
        });
    }
}
