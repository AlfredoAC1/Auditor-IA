<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAnalysis extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
        'organization_id',
        'requested_by',
        'analysis_type',
        'llm_provider',
        'llm_model',
        'content',
        'confidence_score',
        'input_tokens',
        'output_tokens',
        'processing_time_ms',
        'prompt_metadata',
        'status',
        'version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'analysis_type' => AnalysisType::class,
            'status' => DocumentStatus::class,
            'content' => 'array',
            'confidence_score' => 'decimal:2',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'processing_time_ms' => 'integer',
            'prompt_metadata' => 'array',
            'version' => 'integer',
        ];
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

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
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to analyses of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeOfType(
        \Illuminate\Database\Eloquent\Builder $query,
        AnalysisType $type,
    ): \Illuminate\Database\Eloquent\Builder {
        return $query->where('analysis_type', $type);
    }

    /**
     * Scope to a specific organization.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeForOrganization(
        \Illuminate\Database\Eloquent\Builder $query,
        int $organizationId,
    ): \Illuminate\Database\Eloquent\Builder {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to only the latest version of each analysis.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeLatestVersion(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderByDesc('version');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Get the total tokens consumed by this analysis.
     */
    public function totalTokens(): int
    {
        return $this->input_tokens + $this->output_tokens;
    }

    /**
     * Determine if this analysis completed successfully.
     */
    public function isSuccessful(): bool
    {
        return $this->status === DocumentStatus::Processed;
    }
}
