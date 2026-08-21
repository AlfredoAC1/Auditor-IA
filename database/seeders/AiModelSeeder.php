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

        $this->command?->info('AI models seeded: ' . count($models) . ' models created/updated.');
    }

    /**
     * Define the available AI models.
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
            // Lightweight (1x credits) — Free tier default
            // ---------------------------------------------------------------
            [
                'slug' => 'llama-8b-instant',
                'display_name' => 'Llama 3.1 8B',
                'provider' => 'groq',
                'provider_model_id' => 'llama-3.1-8b-instant',
                'tier' => AiModelTier::Lightweight,
                'max_tokens' => 4096,
                'is_active' => true,
            ],

            // ---------------------------------------------------------------
            // Standard (2x credits) — Pro/Lifetime
            // ---------------------------------------------------------------
            [
                'slug' => 'llama-70b-versatile',
                'display_name' => 'Llama 3.3 70B',
                'provider' => 'groq',
                'provider_model_id' => 'llama-3.3-70b-versatile',
                'tier' => AiModelTier::Standard,
                'max_tokens' => 8192,
                'is_active' => true,
            ],

            // ---------------------------------------------------------------
            // Advanced (3x credits) — Pro/Lifetime
            // ---------------------------------------------------------------
            [
                'slug' => 'nvidia-llama-70b',
                'display_name' => 'Llama 3.3 70B (NIM)',
                'provider' => 'nvidia_nim',
                'provider_model_id' => 'meta/llama-3.3-70b-instruct',
                'tier' => AiModelTier::Advanced,
                'max_tokens' => 4096,
                'is_active' => true,
            ],
            [
                'slug' => 'deepseek-chat',
                'display_name' => 'DeepSeek V3',
                'provider' => 'deepseek',
                'provider_model_id' => 'deepseek-chat',
                'tier' => AiModelTier::Advanced,
                'max_tokens' => 4096,
                'is_active' => false, // Enable when API key is configured
            ],

            // ---------------------------------------------------------------
            // Premium (4x credits) — Pro/Lifetime
            // ---------------------------------------------------------------
            [
                'slug' => 'azure-gpt4',
                'display_name' => 'GPT-4',
                'provider' => 'azure',
                'provider_model_id' => 'gpt-4',
                'tier' => AiModelTier::Premium,
                'max_tokens' => 4096,
                'is_active' => false, // Enable when Azure is configured
            ],
        ];
    }
}
