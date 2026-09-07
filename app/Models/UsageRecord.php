<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UsageType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageRecord extends Model
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
        'usage_type',
        'quantity',
        'metadata',
        'period_start',
        'period_end',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'usage_type' => UsageType::class,
            'quantity' => 'integer',
            'metadata' => 'array',
            'period_start' => 'date',
            'period_end' => 'date',
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

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to records within a billing period.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForPeriod(
        Builder $query,
        \DateTimeInterface $periodStart,
        \DateTimeInterface $periodEnd,
    ): Builder {
        return $query
            ->where('period_start', '>=', $periodStart)
            ->where('period_end', '<=', $periodEnd);
    }

    /**
     * Scope to a specific usage type.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfType(
        Builder $query,
        UsageType $type,
    ): Builder {
        return $query->where('usage_type', $type);
    }

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
}
