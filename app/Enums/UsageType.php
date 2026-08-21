<?php

declare(strict_types=1);

namespace App\Enums;

enum UsageType: string
{
    case DocumentUpload = 'document_upload';
    case StorageBytes = 'storage_bytes';
    case AiCreditsConsumed = 'ai_credits_consumed';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DocumentUpload => 'Subida de documento',
            self::StorageBytes => 'Almacenamiento usado',
            self::AiCreditsConsumed => 'Créditos IA consumidos',
        };
    }

    /**
     * Get the unit of measurement for this usage type.
     */
    public function unit(): string
    {
        return match ($this) {
            self::DocumentUpload => 'documentos',
            self::StorageBytes => 'bytes',
            self::AiCreditsConsumed => 'créditos',
        };
    }
}
