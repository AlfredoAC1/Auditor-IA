<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User  = 'user';

    /**
     * Get human-readable label in Spanish.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::User  => 'Usuario',
        };
    }

    /**
     * Get styling badge classes for neo-brutalist theme.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Admin => 'bg-[#A4D8FF]/20 text-[#A4D8FF] border border-[#A4D8FF]/40',
            self::User  => 'bg-gunmetal-card text-icy/70 border border-gunmetal',
        };
    }
}
