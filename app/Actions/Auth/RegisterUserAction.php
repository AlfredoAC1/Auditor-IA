<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterUserDTO;
use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class RegisterUserAction
{
    /**
     * Register a new user and provision their default organization and credit ledger.
     *
     * Workflow:
     * 1. Create a dedicated Organization for the user.
     * 2. Create the User linked to the organization.
     * 3. Provision the initial Free tier AI credit ledger & audit transaction.
     * 4. Structured audit logging.
     */
    public function execute(RegisterUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            // 1. Resolve organization name and unique slug
            $orgName = ! empty($dto->organizationName)
                ? $dto->organizationName
                : "Espacio de {$dto->name}";

            $baseSlug = Str::slug($orgName);
            $slug = $baseSlug.'-'.Str::lower(Str::random(6));

            $organization = Organization::create([
                'uuid' => (string) Str::uuid(),
                'name' => $orgName,
                'slug' => $slug,
                'is_active' => true,
            ]);

            // 2. Create user bound to organization
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $dto->password,
                'organization_id' => $organization->id,
            ]);

            // 3. Provision initial credit ledger and record audit trail
            $ledger = $organization->getOrCreateCreditLedger();

            CreditTransaction::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'type' => CreditTransactionType::QuotaRefresh,
                'credits_amount' => $ledger->credits_quota,
                'credits_balance_after' => $ledger->credits_remaining,
                'metadata' => [
                    'plan' => 'free',
                    'reason' => 'initial_registration',
                ],
                'created_at' => now(),
            ]);

            // 4. Structured audit log
            Log::info('User registered successfully.', [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'email' => $user->email,
            ]);

            return $user;
        });
    }
}
