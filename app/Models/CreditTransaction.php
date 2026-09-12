<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiModelTier;
use App\Enums\CreditTransactionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit log entry for every AI credit movement.
 *
 * Records debits (analysis consumption), credits (quota refresh,
 * admin adjustments), and refunds. Each transaction captures
 * a snapshot of the balance after the operation.
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $user_id
 * @property CreditTransactionType $type
 * @property int $credits_amount
 * @property int $credits_balance_after
 * @property string|null $model_tier
 * @property string|null $analysis_id
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 */
class CreditTransaction extends Model
{
    use HasFactory;

    /**
     * Disable default timestamps — this model only has created_at.
     */
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'type',
        'credits_amount',
        'credits_balance_after',
        'model_tier',
        'analysis_id',
        'metadata',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CreditTransactionType::class,
            'credits_amount' => 'integer',
            'credits_balance_after' => 'integer',
            'model_tier' => AiModelTier::class,
            'metadata' => 'array',
            'created_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<DocumentAnalysis, $this>
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(DocumentAnalysis::class, 'analysis_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to a specific organization.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForOrganization(
        Builder $query,
        int $organizationId,
    ): Builder {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to a specific transaction type.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfType(
        Builder $query,
        CreditTransactionType $type,
    ): Builder {
        return $query->where('type', $type);
    }

    /**
     * Scope to only debit transactions.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('type', CreditTransactionType::AnalysisDebit);
    }

    /**
     * Scope to transactions within a date range.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForPeriod(
        Builder $query,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
    ): Builder {
        return $query
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to);
    }
}
