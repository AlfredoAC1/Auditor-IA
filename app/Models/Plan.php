<?php

declare(strict_types=1);

namespace App\Models;

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
        'max_analyses_per_month',
        'max_storage_bytes',
        'max_tokens_per_month',
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
            'max_analyses_per_month' => 'integer',
            'max_storage_bytes' => 'integer',
            'max_tokens_per_month' => 'integer',
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
}
