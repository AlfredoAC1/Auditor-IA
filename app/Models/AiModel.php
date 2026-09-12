<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiModelTier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Registry of available AI models for document analysis.
 *
 * Each model belongs to a tier that determines credit consumption.
 * Providers are tracked internally but hidden from free-tier users.
 *
 * @property int $id
 * @property string $slug
 * @property string $display_name
 * @property string $provider
 * @property string $provider_model_id
 * @property AiModelTier $tier
 * @property float $credit_multiplier
 * @property int $max_tokens
 * @property bool $is_active
 * @property int $sort_order
 */
class AiModel extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'display_name',
        'provider',
        'provider_model_id',
        'tier',
        'credit_multiplier',
        'max_tokens',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => AiModelTier::class,
            'credit_multiplier' => 'float',
            'max_tokens' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to only active models.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to models of a specific tier.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfTier(
        Builder $query,
        AiModelTier $tier,
    ): Builder {
        return $query->where('tier', $tier);
    }

    /**
     * Scope to models within a set of allowed tiers.
     *
     * @param  Builder<static>  $query
     * @param  list<AiModelTier>  $tiers
     * @return Builder<static>
     */
    public function scopeWithinTiers(
        Builder $query,
        array $tiers,
    ): Builder {
        $tierValues = array_map(fn (AiModelTier $tier) => $tier->value, $tiers);

        return $query->whereIn('tier', $tierValues);
    }

    /**
     * Scope to models ordered by sort_order.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Calculate credit cost based on token consumption and model weight.
     * Formula: ((total_tokens) / tokens_per_credit) * weight
     */
    public function calculateCreditCost(int $totalTokens): int
    {
        $tokensPerCredit = (int) config('billing.credits.tokens_per_credit', 175);
        $minCharge = (int) config('billing.credits.min_charge', 1);

        $rawCredits = ($totalTokens / $tokensPerCredit) * $this->credit_multiplier;

        return max($minCharge, (int) ceil($rawCredits));
    }

    /**
     * Calculate the credit cost for a single analysis using this model.
     * If total tokens are provided, calculates dynamically based on formula;
     * otherwise returns the base multiplier.
     */
    public function creditCostPerAnalysis(?int $totalTokens = null): int
    {
        if ($totalTokens !== null) {
            return $this->calculateCreditCost($totalTokens);
        }

        return (int) ceil($this->credit_multiplier);
    }

    /**
     * Get the LLM config key for this model's provider.
     *
     * Used to resolve provider configuration from config/llm.php.
     */
    public function providerConfigKey(): string
    {
        return "llm.providers.{$this->provider}";
    }

    /**
     * Find a model by its slug or fail.
     */
    public static function findBySlug(string $slug): self
    {
        return static::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Get the default model (first active model, excluding llama during testing).
     */
    public static function defaultModel(): self
    {
        return static::query()
            ->active()
            ->where('slug', 'not like', '%llama%')
            ->where('provider_model_id', 'not like', '%llama%')
            ->ordered()
            ->first()
            ?? static::query()->active()->ordered()->firstOrFail();
    }
}
