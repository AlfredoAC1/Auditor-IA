<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';

    /**
     * Get human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Processing => 'En procesamiento',
            self::Processed => 'Procesado',
            self::Failed => 'Fallido',
        };
    }

    /**
     * Determine if the document can transition to the given status.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Processing, self::Failed],
            self::Processing => [self::Processed, self::Failed],
            self::Processed => [], // Terminal state
            self::Failed => [self::Pending], // Allow retry
        };
    }

    /**
     * Check if a transition to the given status is valid.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
