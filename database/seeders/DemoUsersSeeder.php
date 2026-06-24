<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SubscriptionStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds demo Free and Pro users for local development and testing.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();
        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();

        // Free tier demo user
        $freeOrg = Organization::updateOrCreate(
            ['slug' => 'demo-free'],
            [
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

        // Pro tier demo user
        $proOrg = Organization::updateOrCreate(
            ['slug' => 'demo-pro'],
            [
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

        $this->command?->info('Demo users seeded:');
        $this->command?->line('  Free → free@auditor-ia.test / password');
        $this->command?->line('  Pro  → pro@auditor-ia.test / password');
    }
}
