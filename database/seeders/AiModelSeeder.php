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
                    'display_name'      => $model['display_name'],
                    'provider'          => $model['provider'],
                    'provider_model_id' => $model['provider_model_id'],
                    'tier'              => $model['tier'],
                    'credit_multiplier' => $model['tier']->creditMultiplier(),
                    'max_tokens'        => $model['max_tokens'],
                    'is_active'         => $model['is_active'],
                    'sort_order'        => $sortOrder++,
                ],
            );
        }

        $this->command?->info('AI models seeded: ' . count($models) . ' models created/updated.');
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
            // Lightweight (1x credits) — Free tier default
            // Fast, efficient models for everyday document analysis
            // ---------------------------------------------------------------
            [
                'slug'              => 'llama-8b-instant',
                'display_name'      => 'Llama 3.1 8B',
                'provider'          => 'groq',
                'provider_model_id' => 'llama-3.1-8b-instant',
                'tier'              => AiModelTier::Lightweight,
                'max_tokens'        => 4096,
                'is_active'         => true,
            ],
            [
                'slug'              => 'qwen3-8b',
                'display_name'      => 'Qwen 3 8B',
                'provider'          => 'qwen_cloud',
                'provider_model_id' => 'qwen3-8b',
                'tier'              => AiModelTier::Lightweight,
                'max_tokens'        => 8192,
                'is_active'         => false, // Enable when QWEN_API_KEY is set
            ],
            [
                'slug'              => 'qwen2-5-7b',
                'display_name'      => 'Qwen 2.5 7B',
                'provider'          => 'qwen_cloud',
                'provider_model_id' => 'qwen2.5-7b-instruct',
                'tier'              => AiModelTier::Lightweight,
                'max_tokens'        => 8192,
                'is_active'         => false, // Enable when QWEN_API_KEY is set
            ],

            // ---------------------------------------------------------------
            // Standard (2x credits) — Pro/Lifetime
            // Balanced models with strong reasoning and context handling
            // ---------------------------------------------------------------
            [
                'slug'              => 'llama-70b-versatile',
                'display_name'      => 'Llama 3.3 70B',
                'provider'          => 'groq',
                'provider_model_id' => 'llama-3.3-70b-versatile',
                'tier'              => AiModelTier::Standard,
                'max_tokens'        => 8192,
                'is_active'         => true,
            ],
            [
                'slug'              => 'cerebras-gemma4',
                'display_name'      => 'Gemma 4 31B (Preview)',
                'provider'          => 'cerebras',
                'provider_model_id' => 'gemma-4-31b',
                'tier'              => AiModelTier::Standard,
                // gemma-4-31b Preview hard cap: 40,960 tokens.
                // 32,768 is the configured operational ceiling.
                'max_tokens'        => 32768,
                'is_active'         => true, // CEREBRAS_API_KEY is set
            ],
            [
                'slug'              => 'qwen3-14b',
                'display_name'      => 'Qwen 3 14B',
                'provider'          => 'qwen_cloud',
                'provider_model_id' => 'qwen3-14b',
                'tier'              => AiModelTier::Standard,
                'max_tokens'        => 8192,
                'is_active'         => false, // Enable when QWEN_API_KEY is set
            ],

            // ---------------------------------------------------------------
            // Advanced (3x credits) — Pro/Lifetime
            // High-quality models with deep legal reasoning capabilities
            // ---------------------------------------------------------------
            [
                'slug'              => 'deepseek-v3',
                'display_name'      => 'DeepSeek V3',
                'provider'          => 'deepseek',
                'provider_model_id' => 'deepseek-chat',
                'tier'              => AiModelTier::Advanced,
                'max_tokens'        => 8192,
                'is_active'         => false, // Enable when DEEPSEEK_API_KEY is set
            ],
            [
                'slug'              => 'qwen-plus',
                'display_name'      => 'Qwen Plus',
                'provider'          => 'qwen_cloud',
                'provider_model_id' => 'qwen-plus',
                'tier'              => AiModelTier::Advanced,
                'max_tokens'        => 8192,
                'is_active'         => false, // Enable when QWEN_API_KEY is set
            ],
            [
                'slug'              => 'nvidia-llama-70b',
                'display_name'      => 'Llama 3.3 70B (NIM)',
                'provider'          => 'nvidia_nim',
                'provider_model_id' => 'meta/llama-3.3-70b-instruct',
                'tier'              => AiModelTier::Advanced,
                'max_tokens'        => 4096,
                'is_active'         => true,
            ],

            // ---------------------------------------------------------------
            // Premium (4x credits) — Pro/Lifetime
            // Frontier & experimental models for critical analysis
            // ---------------------------------------------------------------
            [
                'slug'              => 'deepseek-v4',
                'display_name'      => 'DeepSeek V4',
                'provider'          => 'deepseek',
                'provider_model_id' => 'deepseek-reasoner',
                'tier'              => AiModelTier::Premium,
                'max_tokens'        => 8192,
                'is_active'         => false, // Future: when available + DEEPSEEK_API_KEY
            ],
            [
                'slug'              => 'qwen3-5',
                'display_name'      => 'Qwen 3.5',
                'provider'          => 'qwen_cloud',
                'provider_model_id' => 'qwen-max',
                'tier'              => AiModelTier::Premium,
                'max_tokens'        => 8192,
                'is_active'         => false, // Future: when QWEN_API_KEY is set
            ],
            [
                'slug'              => 'azure-gpt4',
                'display_name'      => 'GPT-4',
                'provider'          => 'azure',
                'provider_model_id' => 'gpt-4',
                'tier'              => AiModelTier::Premium,
                'max_tokens'        => 4096,
                'is_active'         => false, // Enable when Azure is configured
            ],
        ];
    }
}
