<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            Plan::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $planData['name'],
                    'slug' => $slug,
                    'description' => $planData['description'] ?? null,
                    'price_clp' => $planData['price_clp'] ?? 0,
                    'is_free' => $planData['is_free'] ?? false,
                    'max_documents_per_month' => $planData['max_documents_per_month'] ?? null,
                    'max_analyses_per_month' => $planData['max_analyses_per_month'] ?? null,
                    'max_storage_bytes' => $planData['max_storage_bytes'] ?? null,
                    'max_tokens_per_month' => $planData['max_tokens_per_month'] ?? null,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ],
            );
        }

        $this->command->info('Plans seeded: ' . count($plans) . ' plans created/updated.');
    }
}
