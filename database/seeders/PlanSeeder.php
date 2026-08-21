<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds the plans table from config/billing.php.
 *
 * Safe to run multiple times — uses updateOrCreate on slug.
 */
class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = (array) config('billing.plans', []);
        $sortOrder = 0;

        foreach ($plans as $slug => $planData) {
            $monthlyPrice = $planData['price_monthly_clp']
                ?? $planData['price_clp']
                ?? ($planData['price_lifetime_clp'] ?? 0);

            Plan::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $planData['name'],
                    'slug' => $slug,
                    'description' => $planData['description'] ?? null,
                    'price_clp' => $monthlyPrice ?? 0,
                    'is_free' => $planData['is_free'] ?? false,
                    'max_documents_per_month' => $planData['max_documents_per_month'] ?? null,
                    'max_storage_bytes' => $planData['max_storage_bytes'] ?? null,
                    'ai_credits_quota' => $planData['ai_credits_quota'] ?? null,
                    'credit_refresh_interval' => $planData['credit_refresh_interval'] ?? 'monthly',
                    'allowed_model_tiers' => $planData['allowed_model_tiers'] ?? null,
                    'features' => [
                        'billing_type' => $planData['billing_type'] ?? 'recurring',
                        'price_yearly_clp' => $planData['price_yearly_clp'] ?? null,
                        'price_lifetime_clp' => $planData['price_lifetime_clp'] ?? null,
                        'highlights' => $planData['highlights'] ?? [],
                        'popular' => $planData['popular'] ?? false,
                    ],
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ],
            );
        }

        $this->command->info('Plans seeded: ' . count($plans) . ' plans created/updated.');
    }
}
