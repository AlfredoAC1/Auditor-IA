<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model representing a supporting document attached to an analysis.
 *
 * Each record links a DocumentAnalysis to an additional Document that provides
 * complementary legal context. The AI service can access all supporting
 * documents when performing follow-up analyses to preserve full context.
 *
 * @property string $id
 * @property string $analysis_id
 * @property string $document_id
 * @property int $organization_id
 * @property int|null $attached_by
 * @property string|null $relationship_type
 * @property string|null $notes
 */
class AnalysisSupportingDocument extends Model
{
    use HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'analysis_id',
        'document_id',
        'organization_id',
        'attached_by',
        'relationship_type',
        'notes',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    /**
     * @return BelongsTo<DocumentAnalysis, $this>
     */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(DocumentAnalysis::class);
    }

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
    public function attacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attached_by');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to supporting documents belonging to a specific organization.
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

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Get the human-readable label for the relationship type.
     */
    public function relationshipLabel(): string
    {
        return match ($this->relationship_type) {
            'referenced'    => 'Referenciado',
            'related'       => 'Relacionado',
            'clarification' => 'Aclaración',
            'complementary' => 'Complementario',
            default         => 'Adjunto',
        };
    }
}
