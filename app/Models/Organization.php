<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CreditRefreshInterval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'tax_id',
        'is_active',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Boot the model and auto-generate UUID on creation.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Organization $organization): void {
            if (empty($organization->uuid)) {
                $organization->uuid = (string) Str::uuid();
            }

            if (empty($organization->slug)) {
                $organization->slug = Str::slug($organization->name);
            }
        });
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the currently active subscription.
     *
     * @return HasOne<Subscription, $this>
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trialing'])
            ->latest();
    }

    /**
     * @return HasMany<UsageRecord, $this>
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * @return HasMany<DocumentAnalysis, $this>
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(DocumentAnalysis::class);
    }

    /**
     * @return HasOne<AiCreditLedger, $this>
     */
    public function creditLedger(): HasOne
    {
        return $this->hasOne(AiCreditLedger::class);
    }

    /**
     * @return HasMany<CreditTransaction, $this>
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to only active organizations.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Get the active plan for this organization.
     * Returns null if no active subscription exists.
     */
    public function activePlan(): ?Plan
    {
        return $this->activeSubscription?->plan;
    }

    /**
     * Resolve the effective plan for this organization.
     */
    public function currentPlan(): Plan
    {
        $plan = $this->activePlan();

        if ($plan !== null) {
            return $plan;
        }

        return Plan::freePlan()
            ?? Plan::query()->where('slug', config('billing.default_plan', 'free'))->first()
            ?? new Plan(['slug' => 'free', 'name' => 'Free']);
    }

    /**
     * Get the slug of the organization's current plan.
     */
    public function currentPlanSlug(): string
    {
        return $this->currentPlan()->slug;
    }

    /**
     * Determine if the organization has Pro-level access (Pro or Lifetime).
     */
    public function hasProAccess(): bool
    {
        return in_array($this->currentPlanSlug(), ['pro', 'lifetime'], true);
    }

    /**
     * Get or create the AI credit ledger for this organization.
     *
     * Lazily provisions the ledger on first access using
     * the organization's current plan quota.
     */
    public function getOrCreateCreditLedger(): AiCreditLedger
    {
        $plan = $this->currentPlan();
        $quota = $plan->ai_credits_quota ?? 0;

        return $this->creditLedger()->firstOrCreate(
            ['organization_id' => $this->id],
            [
                'credits_remaining' => $quota,
                'credits_quota' => $quota,
                'period_started_at' => now(),
                'period_expires_at' => $this->calculatePeriodExpiry($plan),
            ],
        );
    }

    /**
     * Calculate when the credit period expires based on the plan's refresh interval.
     */
    private function calculatePeriodExpiry(Plan $plan): Carbon
    {
        return match ($plan->credit_refresh_interval) {
            CreditRefreshInterval::Weekly => now()->addDays(7),
            CreditRefreshInterval::Monthly => now()->addMonth(),
            default => now()->addMonth(),
        };
    }
}
