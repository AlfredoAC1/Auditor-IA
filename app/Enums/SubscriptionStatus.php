<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Trialing = 'trialing';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Incomplete = 'incomplete';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Trialing => 'Período de prueba',
            self::PastDue => 'Pago pendiente',
            self::Cancelled => 'Cancelada',
            self::Incomplete => 'Incompleta',
        };
    }

    /**
     * Determine if the subscription allows usage.
     */
    public function allowsUsage(): bool
    {
        return in_array($this, [self::Active, self::Trialing], true);
    }
}
