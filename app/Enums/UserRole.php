<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Auditor = 'auditor';
    case User = 'user';

    /**
     * Get human-readable label in Spanish.
     */
    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Propietario',
            self::Admin => 'Administrador',
            self::Auditor => 'Auditor Legal',
            self::User => 'Usuario',
        };
    }

    /**
     * Get styling badge classes for neo-brutalist theme.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Owner => 'bg-[#E5C07B]/20 text-[#E5C07B] border border-[#E5C07B]/40',
            self::Admin => 'bg-[#A4D8FF]/20 text-[#A4D8FF] border border-[#A4D8FF]/40',
            self::Auditor => 'bg-[#98C379]/20 text-[#98C379] border border-[#98C379]/40',
            self::User => 'bg-gunmetal-card text-icy/70 border border-gunmetal',
        };
    }

    /**
     * Get numerical hierarchy rank for privilege checks.
     */
    public function hierarchyRank(): int
    {
        return match ($this) {
            self::Owner => 40,
            self::Admin => 30,
            self::Auditor => 20,
            self::User => 10,
        };
    }

    /**
     * Determine if this role can manage other users.
     */
    public function canManageUsers(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    /**
     * Determine if this role can manage subscription/billing.
     */
    public function canManageBilling(): bool
    {
        return $this === self::Owner;
    }

    /**
     * Determine if this role can alter organization settings.
     */
    public function canManageSettings(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    /**
     * Determine if this role can upload, mutate, or delete documents.
     * Auditors have read-only access to preserve forensic legal integrity.
     */
    public function canMutateDocuments(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::User], true);
    }
}
