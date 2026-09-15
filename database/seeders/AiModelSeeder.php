<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AiModelTier;
use App\Models\AiModel;
use Illuminate\Database\Seeder;

/**
 * Seeds the AI models registry with available models.
 *
 * Models are mapped to providers and tiers that determine
 * credit consumption. Providers are internal — not exposed
 * to users in the UI yet.
 *
 * Safe to run multiple times — uses updateOrCreate on slug.
 */
class AiModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = $this->getModels();
        $sortOrder = 0;

        foreach ($models as $model) {
            AiModel::updateOrCreate(
                ['slug' => $model['slug']],
                [
                    'display_name' => $model['display_name'],
                    'provider' => $model['provider'],
                    'provider_model_id' => $model['provider_model_id'],
                    'tier' => $model['tier'],
                    'credit_multiplier' => $model['tier']->creditMultiplier(),
                    'max_tokens' => $model['max_tokens'],
                    'is_active' => $model['is_active'],
                    'sort_order' => $sortOrder++,
                ],
            );
        }

        $this->command?->info('AI models seeded: '.count($models).' models created/updated.');
    }

    /**
     * Define the available AI models.
     *
     * Tier structure:
     *   Lightweight (1x) — Free default, fast inference
     *   Standard    (2x) — Pro/Lifetime, balanced quality
     *   Advanced    (3x) — Pro/Lifetime, high quality
     *   Premium     (4x) — Pro/Lifetime, frontier models
     *
     * @return list<array{
     *     slug: string,
     *     display_name: string,
     *     provider: string,
     *     provider_model_id: string,
     *     tier: AiModelTier,
     *     max_tokens: int,
     *     is_active: bool,
     * }>
     */
    private function getModels(): array
    {
        return [
            // ---------------------------------------------------------------
            // Lightweight (1x credits) — Free tier default & Pro Lightweight
            // ---------------------------------------------------------------
            [
                'slug' => 'cerebras-gpt-oss-20b',
                'display_name' => 'GPT-OSS 20B (Cerebras)',
                'provider' => 'cerebras',
                'provider_model_id' => 'gpt-oss-20b',
                'tier' => AiModelTier::Lightweight,
                'max_tokens' => 8192,
                'is_active' => true, // Exclusively for Free Tier
            ],
            [
                'slug' => 'qwen3-14b',
                'display_name' => 'Qwen 3 14B',
                'provider' => 'qwen_cloud',
                'provider_model_id' => 'qwen3-14b',
                'tier' => AiModelTier::Lightweight,
                'max_tokens' => 4096,
                'is_active' => true, // Pro/Lifetime Lightweight
            ],

            // ---------------------------------------------------------------
            // Standard (2x credits) — Pro/Lifetime Standard
            // ---------------------------------------------------------------
            [
                'slug' => 'qwen3-max',
                'display_name' => 'Qwen 3 Max',
                'provider' => 'qwen_cloud',
                'provider_model_id' => 'qwen3-max',
                'tier' => AiModelTier::Standard,
                'max_tokens' => 8192,
                'is_active' => true, // Pro/Lifetime Standard
            ],

            // ---------------------------------------------------------------
            // Advanced (3x credits) — Pro/Lifetime Advanced
            // ---------------------------------------------------------------
            [
                'slug' => 'qwen3-5-flash',
                'display_name' => 'Qwen 3.5 Flash',
                'provider' => 'qwen_cloud',
                'provider_model_id' => 'qwen3.5-flash',
                'tier' => AiModelTier::Advanced,
                'max_tokens' => 16384,
                'is_active' => true, // Pro/Lifetime Advanced
            ],

            // ---------------------------------------------------------------
            // Premium (4x credits) — Pro/Lifetime Premium
            // ---------------------------------------------------------------
            [
                'slug' => 'qwen-3-7-flash',
                'display_name' => 'Qwen 3.7 Flash',
                'provider' => 'qwen_cloud',
                'provider_model_id' => 'qwen-3.7-flash',
                'tier' => AiModelTier::Premium,
                'max_tokens' => 32768,
                'is_active' => true, // Pro/Lifetime Premium
            ],
        ];
    }
}
