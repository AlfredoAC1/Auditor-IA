<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\CreateUserDTO;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class CreateUserAction
{
    /**
     * Create a user or administrator, assigning an existing or newly provisioned organization.
     */
    public function execute(CreateUserDTO $dto, ?User $adminUser = null): User
    {
        return DB::transaction(function () use ($dto, $adminUser): User {
            $organizationId = $dto->organizationId;

            // Provision a new organization if requested or none provided
            if ($organizationId === null) {
                $orgName = ! empty($dto->newOrganizationName)
                    ? $dto->newOrganizationName
                    : "Organización de {$dto->name}";

                $baseSlug = Str::slug($orgName);
                $slug = $baseSlug.'-'.Str::lower(Str::random(6));

                $org = Organization::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $orgName,
                    'slug' => $slug,
                    'is_active' => true,
                ]);

                $org->getOrCreateCreditLedger();
                $organizationId = $org->id;
            }

            $user = new User;
            $user->name = $dto->name;
            $user->email = $dto->email;
            $user->password = $dto->password;
            $user->role = $dto->role;
            $user->organization_id = $organizationId;
            $user->save();

            Log::info('Admin created user.', [
                'admin_id' => $adminUser?->id,
                'created_user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->value,
                'organization_id' => $user->organization_id,
            ]);

            return $user;
        });
    }
}
