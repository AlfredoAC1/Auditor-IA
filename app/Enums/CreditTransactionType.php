<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Types of credit transactions recorded in the credit ledger.
 *
 * Every credit movement is immutably logged with its type
 * for full audit trail compliance.
 */
enum CreditTransactionType: string
{
    case AnalysisDebit = 'analysis_debit';
    case QuotaRefresh = 'quota_refresh';
    case AdminAdjustment = 'admin_adjustment';
    case QuotaExceededRefund = 'quota_exceeded_refund';
    case PlanUpgrade = 'plan_upgrade';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::AnalysisDebit => 'Consumo por análisis',
            self::QuotaRefresh => 'Renovación de cuota',
            self::AdminAdjustment => 'Ajuste administrativo',
            self::QuotaExceededRefund => 'Reembolso por cuota excedida',
            self::PlanUpgrade => 'Actualización de plan',
        };
    }

    /**
     * Determine if this transaction type increases credits.
     */
    public function isCredit(): bool
    {
        return in_array($this, [
            self::QuotaRefresh,
            self::AdminAdjustment,
            self::QuotaExceededRefund,
            self::PlanUpgrade,
        ], true);
    }

    /**
     * Determine if this transaction type decreases credits.
     */
    public function isDebit(): bool
    {
        return $this === self::AnalysisDebit;
    }
}
