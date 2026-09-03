<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organization_id',
        'dismissed_tutorial',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dismissed_tutorial' => 'boolean',
            'role' => UserRole::class,
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
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    /**
     * @return HasMany<DocumentAnalysis, $this>
     */
    public function analyses(): HasMany
    {
        return $this->hasMany(DocumentAnalysis::class, 'requested_by');
    }

    /**
     * @return HasMany<BugTicket, $this>
     */
    public function bugTickets(): HasMany
    {
        return $this->hasMany(BugTicket::class);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Determine if the user has administrator privileges.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Determine if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === UserRole::User;
    }

    /**
     * Determine if the user belongs to any organization.
     */
    public function hasOrganization(): bool
    {
        return $this->organization_id !== null;
    }

    /**
     * Determine if the user belongs to the given organization.
     */
    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    /**
     * Determine if the user's organization has Pro-level access.
     */
    public function hasProAccess(): bool
    {
        return $this->organization?->hasProAccess() ?? false;
    }

    /**
     * Return a temporary signed URL for the user's private avatar,
     * or null if no avatar has been set.
     */
    public function avatarUrl(): ?string
    {
        if ($this->avatar_path === null) {
            return null;
        }

        // Serve via signed route so the private file is never directly exposed.
        return route('settings.avatar.serve', ['user' => $this->id]);
    }

    /**
     * Return the two-letter initials used as an avatar fallback.
     */
    public function initials(): string
    {
        return strtoupper(mb_substr($this->name, 0, 2));
    }
}
