<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'storage_path',
        'disk',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'status',
        'document_type',
        'metadata',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'document_type' => DocumentType::class,
            'metadata' => 'array',
            'file_size_bytes' => 'integer',
            'processed_at' => 'datetime',
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
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return HasMany<DocumentChunk, $this>
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class)->orderBy('chunk_index');
    }

    /**
     * @return HasMany<DocumentAnalysis, $this>
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(DocumentAnalysis::class);
    }

    /**
     * Get the latest processed analysis for this document.
     *
     * @return HasOne<DocumentAnalysis, $this>
     */
    public function latestAnalysis(): HasOne
    {
        return $this->hasOne(DocumentAnalysis::class)->ofMany([
            'created_at' => 'max',
            'id' => 'max',
        ], function ($query) {
            $query->where('status', DocumentStatus::Processed);
        });
    }

    /**
     * Get records where this document acts as a supporting document for other analyses.
     *
     * @return HasMany<AnalysisSupportingDocument, $this>
     */
    public function supportingIn(): HasMany
    {
        return $this->hasMany(AnalysisSupportingDocument::class, 'document_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to documents belonging to a specific organization.
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
     * Scope to documents with a specific status.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithStatus(
        Builder $query,
        DocumentStatus $status,
    ): Builder {
        return $query->where('status', $status);
    }

    // ---------------------------------------------------------------
    // Business Logic
    // ---------------------------------------------------------------

    /**
     * Check if the document can transition to the given status.
     */
    public function canTransitionTo(DocumentStatus $target): bool
    {
        return $this->status->canTransitionTo($target);
    }

    /**
     * Transition the document to a new status with validation.
     *
     * @throws \InvalidArgumentException
     */
    public function transitionTo(DocumentStatus $target): void
    {
        if (! $this->canTransitionTo($target)) {
            throw new \InvalidArgumentException(
                "Cannot transition document [{$this->id}] from [{$this->status->value}] to [{$target->value}]."
            );
        }

        $this->update(['status' => $target]);
    }

    /**
     * Move the document into processing, including queue retries from failed state.
     *
     * @throws \InvalidArgumentException
     */
    public function beginProcessing(): void
    {
        if ($this->status === DocumentStatus::Processing) {
            return;
        }

        if ($this->status === DocumentStatus::Failed) {
            $this->transitionTo(DocumentStatus::Pending);
        }

        $this->transitionTo(DocumentStatus::Processing);
    }

    /**
     * Get the full storage path for the document file.
     */
    public function fullStoragePath(): string
    {
        return $this->storage_path.'/'.$this->stored_filename;
    }
}
