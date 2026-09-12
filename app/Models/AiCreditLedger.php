<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tracks the current AI credit balance for an organization.
 *
 * Acts as a denormalized balance cache — the source of truth
 * is the credit_transactions table, but this provides O(1)
 * balance lookups for quota enforcement.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $credits_remaining
 * @property int $credits_quota
 * @property Carbon|null $period_started_at
 * @property Carbon|null $period_expires_at
 * @property Carbon|null $last_usage_at
 */
class AiCreditLedger extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'ai_credit_ledgers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'credits_remaining',
        'credits_quota',
        'period_started_at',
        'period_expires_at',
        'last_usage_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credits_remaining' => 'integer',
            'credits_quota' => 'integer',
            'period_started_at' => 'datetime',
            'period_expires_at' => 'datetime',
            'last_usage_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<CreditTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class, 'organization_id', 'organization_id');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Determine if the organization has enough credits for a given cost.
     */
    public function hasCreditsFor(int $creditCost): bool
    {
        return $this->credits_remaining >= $creditCost;
    }

    /**
     * Determine if the organization has unlimited credits.
     *
     * Unlimited is indicated by credits_quota = 0 (from a plan with NULL quota).
     * In this case credits_remaining is not enforced.
     */
    public function isUnlimited(): bool
    {
        return $this->credits_quota === 0;
    }

    /**
     * Get the usage percentage (0.0 to 1.0).
     *
     * Returns 0.0 for unlimited plans.
     */
    public function usagePercentage(): float
    {
        if ($this->isUnlimited() || $this->credits_quota === 0) {
            return 0.0;
        }

        $consumed = $this->credits_quota - $this->credits_remaining;

        return min(1.0, max(0.0, $consumed / $this->credits_quota));
    }

    /**
     * Determine if the current credit period has expired.
     */
    public function isPeriodExpired(): bool
    {
        if ($this->period_expires_at === null) {
            return false;
        }

        return $this->period_expires_at->isPast();
    }

    /**
     * Determine if credits are depleted (no credits remaining and not unlimited).
     */
    public function isDepleted(): bool
    {
        if ($this->isUnlimited()) {
            return false;
        }

        return $this->credits_remaining <= 0;
    }

    /**
     * Check if the organization can start at least one analysis.
     *
     * Even when credits are low, the system must allow at least
     * one analysis (using the cheapest available model).
     */
    public function canStartMinimumAnalysis(): bool
    {
        if ($this->isUnlimited()) {
            return true;
        }

        // Minimum cost is 1 credit (lightweight model at 1.0x)
        return $this->credits_remaining >= 1;
    }
}
