<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawEmbedding extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'law_identifier',
        'law_name',
        'article_number',
        'book_or_title',
        'content',
        'content_hash',
        'token_count',
        'version',
        'is_current',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token_count' => 'integer',
            'version' => 'integer',
            'is_current' => 'boolean',
            'metadata' => 'array',
        ];
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to only current (latest version) embeddings.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope to a specific law.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForLaw(
        Builder $query,
        string $lawName,
    ): Builder {
        return $query->where('law_name', $lawName);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Determine if the content has changed compared to a new hash.
     */
    public function hasContentChanged(string $newHash): bool
    {
        return $this->content_hash !== $newHash;
    }

    /**
     * Get a formatted citation for this law article.
     * E.g., "Art. 1545, Código Civil de Chile"
     */
    public function citation(): string
    {
        $parts = [];

        if ($this->article_number) {
            $parts[] = "Art. {$this->article_number}";
        }

        $parts[] = $this->law_name;

        return implode(', ', $parts);
    }
}
