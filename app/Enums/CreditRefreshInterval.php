<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Defines how frequently an organization's AI credit quota is refreshed.
 *
 * Free-tier organizations refresh weekly (from last usage date),
 * while Pro and Lifetime refresh monthly (calendar cycle).
 */
enum CreditRefreshInterval: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Monthly => 'Mensual',
        };
    }

    /**
     * Get the number of days in this refresh interval.
     *
     * Used as a fallback; actual refresh dates should be computed
     * from the ledger's period_started_at + business rules.
     */
    public function approximateDays(): int
    {
        return match ($this) {
            self::Weekly => 7,
            self::Monthly => 30,
        };
    }
}
