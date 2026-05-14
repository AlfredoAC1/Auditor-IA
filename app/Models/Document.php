<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to documents belonging to a specific organization.
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
     * Scope to documents with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeWithStatus(
        \Illuminate\Database\Eloquent\Builder $query,
        DocumentStatus $status,
    ): \Illuminate\Database\Eloquent\Builder {
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
     * Get the full storage path for the document file.
     */
    public function fullStoragePath(): string
    {
        return $this->storage_path . '/' . $this->stored_filename;
    }
}
