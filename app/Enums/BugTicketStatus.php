<?php

declare(strict_types=1);

namespace App\Enums;

enum BugTicketStatus: string
{
    case New      = 'new';
    case Resolved = 'resolved';

    /**
     * Get human-readable label in Spanish.
     */
    public function label(): string
    {
        return match ($this) {
            self::New      => 'Nuevo',
            self::Resolved => 'Resuelto',
        };
    }

    /**
     * Get styling badge classes for neo-brutalist theme.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New      => 'bg-amber-500/20 text-amber-300 border border-amber-500/40',
            self::Resolved => 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40',
        };
    }
}
