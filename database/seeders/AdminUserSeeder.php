<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CreditTransactionType;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\CreditTransaction;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        $proPlan = Plan::query()->where('slug', 'pro')->first();

        // 1. Create or retrieve the administrative Organization
        $adminOrg = Organization::updateOrCreate(
            ['slug' => 'auditor-ia-admin'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Auditor-IA Admin Org',
                'is_active' => true,
            ],
        );

        // 2. Create or update the Administrator User
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@auditor-ia.test'],
            [
                'name' => 'Administrador Auditor-IA',
                'password' => 'password',
                'role' => UserRole::Admin,
                'organization_id' => $adminOrg->id,
            ],
        );

        // 3. Ensure active subscription and generous AI credit ledger
        if ($proPlan !== null) {
            Subscription::updateOrCreate(
                ['organization_id' => $adminOrg->id],
                [
                    'plan_id' => $proPlan->id,
                    'status' => SubscriptionStatus::Active,
                    'current_period_start' => now()->startOfMonth(),
                    'current_period_end' => now()->addYear(),
                ],
            );

            $ledger = $adminOrg->getOrCreateCreditLedger();
            $ledger->credits_remaining = 10000;
            $ledger->credits_quota = 10000;
            $ledger->save();

            CreditTransaction::updateOrCreate(
                [
                    'organization_id' => $adminOrg->id,
                    'type' => CreditTransactionType::QuotaRefresh,
                ],
                [
                    'user_id' => $adminUser->id,
                    'credits_amount' => 10000,
                    'credits_balance_after' => 10000,
                    'metadata' => [
                        'plan' => 'admin_unlimited',
                        'reason' => 'admin_seed',
                    ],
                    'created_at' => now(),
                ],
            );
        }

        $this->command?->info('Admin user seeded: admin@auditor-ia.test / password');
    }
}
