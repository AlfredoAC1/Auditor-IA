<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Classification tiers for AI models based on computational power.
 *
 * Each tier maps to a credit multiplier that determines
 * how many AI Credits a single analysis consumes.
 */
enum AiModelTier: string
{
    case Lightweight = 'lightweight';
    case Standard = 'standard';
    case Advanced = 'advanced';
    case Premium = 'premium';

    /**
     * Get the credit multiplier for this model tier.
     *
     * Higher tiers consume proportionally more credits per analysis.
     */
    public function creditMultiplier(): float
    {
        return match ($this) {
            self::Lightweight => 1.0,
            self::Standard => 2.0,
            self::Advanced => 3.0,
            self::Premium => 4.0,
        };
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Lightweight => 'Ligero',
            self::Standard => 'Estándar',
            self::Advanced => 'Avanzado',
            self::Premium => 'Premium',
        };
    }

    /**
     * Get a description of the tier's capabilities.
     */
    public function description(): string
    {
        return match ($this) {
            self::Lightweight => 'Modelos rápidos y eficientes para análisis básicos',
            self::Standard => 'Modelos balanceados para análisis generales',
            self::Advanced => 'Modelos potentes para análisis complejos',
            self::Premium => 'Modelos de última generación para máxima precisión',
        };
    }
}
