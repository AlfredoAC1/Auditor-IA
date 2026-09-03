<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\UpdateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class UpdateUserAction
{
    /**
     * Update user details, role, and credentials with safety checks.
     */
    public function execute(User $targetUser, UpdateUserDTO $dto, ?User $adminUser = null): User
    {
        return DB::transaction(function () use ($targetUser, $dto, $adminUser): User {
            // Safety constraint: Prevent demoting the last active administrator
            if ($targetUser->isAdmin() && $dto->role !== UserRole::Admin) {
                $otherAdminsCount = User::query()
                    ->where('role', UserRole::Admin)
                    ->where('id', '!=', $targetUser->id)
                    ->count();

                if ($otherAdminsCount === 0) {
                    throw ValidationException::withMessages([
                        'role' => 'No puedes revocar el rol de administrador al único administrador del sistema.',
                    ]);
                }
            }

            $oldRole = $targetUser->role?->value;

            $targetUser->name = $dto->name;
            $targetUser->email = $dto->email;
            $targetUser->role = $dto->role;

            if ($dto->organizationId !== null) {
                $targetUser->organization_id = $dto->organizationId;
            }

            if (! empty($dto->password)) {
                $targetUser->password = $dto->password;
            }

            $targetUser->save();

            Log::info('Admin updated user.', [
                'admin_id' => $adminUser?->id,
                'target_user_id' => $targetUser->id,
                'email' => $targetUser->email,
                'old_role' => $oldRole,
                'new_role' => $targetUser->role->value,
                'password_changed' => ! empty($dto->password),
            ]);

            return $targetUser;
        });
    }
}
