<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiModelTier;
use App\Enums\CreditRefreshInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'stripe_price_id',
        'max_documents_per_month',
        'max_storage_bytes',
        'ai_credits_quota',
        'credit_refresh_interval',
        'allowed_model_tiers',
        'features',
        'price_clp',
        'sort_order',
        'is_active',
        'is_free',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_documents_per_month' => 'integer',
            'max_storage_bytes' => 'integer',
            'ai_credits_quota' => 'integer',
            'credit_refresh_interval' => CreditRefreshInterval::class,
            'allowed_model_tiers' => 'array',
            'features' => 'array',
            'price_clp' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_free' => 'boolean',
        ];
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOrdered(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderBy('sort_order');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Check if a specific usage type has a limit.
     * Returns false if the limit is NULL (unlimited).
     */
    public function hasLimit(string $limitColumn): bool
    {
        return $this->{$limitColumn} !== null;
    }

    /**
     * Get the limit for a specific usage type.
     * Returns null for unlimited.
     */
    public function getLimit(string $limitColumn): ?int
    {
        return $this->{$limitColumn};
    }

    /**
     * Get the free plan.
     */
    public static function freePlan(): ?self
    {
        return static::query()->where('is_free', true)->first();
    }

    /**
     * Determine if this plan has unlimited AI credits.
     */
    public function hasUnlimitedCredits(): bool
    {
        return $this->ai_credits_quota === null;
    }

    /**
     * Get the allowed model tiers as AiModelTier enum instances.
     *
     * @return list<AiModelTier>
     */
    public function allowedTiers(): array
    {
        $tiers = $this->allowed_model_tiers ?? [];

        return array_filter(
            array_map(
                fn (string $value) => AiModelTier::tryFrom($value),
                $tiers,
            ),
        );
    }

    /**
     * Determine if this plan allows access to a specific model tier.
     */
    public function allowsTier(AiModelTier $tier): bool
    {
        $allowed = $this->allowed_model_tiers ?? [];

        // No restrictions = all tiers allowed (for unlimited plans)
        if ($allowed === []) {
            return true;
        }

        return in_array($tier->value, $allowed, true);
    }

    /**
     * Determine if this plan allows model selection.
     *
     * Free users can only switch analysis type, not models.
     * Pro/Lifetime users can choose between AI models.
     */
    public function allowsModelSelection(): bool
    {
        return ! $this->is_free;
    }
}
