<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CreditRefreshInterval;
use App\Enums\CreditTransactionType;
use App\Enums\SubscriptionStatus;
use App\Models\AiCreditLedger;
use App\Models\CreditTransaction;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds demo Free and Pro users for local development and testing.
 *
 * Includes provisioning AI credit ledgers and initial
 * quota refresh transactions for audit trail.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);
        $this->call(AiModelSeeder::class);

        $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();
        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();

        // ---------------------------------------------------------------
        // Free tier demo user
        // ---------------------------------------------------------------
        $freeOrg = Organization::updateOrCreate(
            ['slug' => 'demo-free'],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Demo Free Org',
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'free@auditor-ia.test'],
            [
                'name' => 'Usuario Free',
                'password' => 'password',
                'organization_id' => $freeOrg->id,
            ],
        );

        Subscription::query()
            ->where('organization_id', $freeOrg->id)
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])
            ->update(['status' => SubscriptionStatus::Cancelled]);

        // Provision free-tier credit ledger
        $this->provisionCreditLedger($freeOrg, $freePlan);

        // ---------------------------------------------------------------
        // Pro tier demo user
        // ---------------------------------------------------------------
        $proOrg = Organization::updateOrCreate(
            ['slug' => 'demo-pro'],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Demo Pro Org',
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'pro@auditor-ia.test'],
            [
                'name' => 'Usuario Pro',
                'password' => 'password',
                'organization_id' => $proOrg->id,
            ],
        );

        Subscription::updateOrCreate(
            ['organization_id' => $proOrg->id],
            [
                'plan_id' => $proPlan->id,
                'status' => SubscriptionStatus::Active,
                'current_period_start' => now()->startOfMonth(),
                'current_period_end' => now()->endOfMonth(),
            ],
        );

        // Provision pro-tier credit ledger
        $this->provisionCreditLedger($proOrg, $proPlan);

        $this->command?->info('Demo users seeded:');
        $this->command?->line('  Free → free@auditor-ia.test / password (25 AI credits, weekly refresh)');
        $this->command?->line('  Pro  → pro@auditor-ia.test / password (500 AI credits, monthly refresh)');
    }

    /**
     * Create or refresh the AI credit ledger for an organization.
     */
    private function provisionCreditLedger(Organization $organization, Plan $plan): void
    {
        $quota = $plan->ai_credits_quota ?? 0;

        $periodExpiresAt = match ($plan->credit_refresh_interval) {
            CreditRefreshInterval::Weekly => now()->addDays(7),
            CreditRefreshInterval::Monthly => now()->addMonth(),
            default => now()->addMonth(),
        };

        $ledger = AiCreditLedger::updateOrCreate(
            ['organization_id' => $organization->id],
            [
                'credits_remaining' => $quota,
                'credits_quota' => $quota,
                'period_started_at' => now(),
                'period_expires_at' => $periodExpiresAt,
                'last_usage_at' => null,
            ],
        );

        // Record initial quota refresh transaction for audit trail
        CreditTransaction::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'type' => CreditTransactionType::QuotaRefresh,
            ],
            [
                'credits_amount' => $quota,
                'credits_balance_after' => $quota,
                'metadata' => [
                    'plan' => $plan->slug,
                    'reason' => 'initial_seed',
                ],
                'created_at' => now(),
            ],
        );
    }
}
