<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Get all supporting-document pivot records for this analysis.
     *
     * @return HasMany<AnalysisSupportingDocument, $this>
     */
    public function supportingDocuments(): HasMany
    {
        return $this->hasMany(AnalysisSupportingDocument::class, 'analysis_id')
            ->with('document:id,original_filename,status,mime_type,metadata');
    }

    /**
     * Get the Document models attached as supporting documents.
     *
     * @return BelongsToMany<Document, $this>
     */
    public function supportingDocumentFiles(): BelongsToMany
    {
        return $this->belongsToMany(
            Document::class,
            'analysis_supporting_documents',
            'analysis_id',
            'document_id',
        )->withPivot(['relationship_type', 'notes', 'attached_by'])
            ->withTimestamps();
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to analyses of a specific type.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfType(
        Builder $query,
        AnalysisType $type,
    ): Builder {
        return $query->where('analysis_type', $type);
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

    /**
     * Scope to only the latest version of each analysis.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeLatestVersion(Builder $query): Builder
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
