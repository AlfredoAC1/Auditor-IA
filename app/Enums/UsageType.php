<?php

declare(strict_types=1);

namespace App\Enums;

enum UsageType: string
{
    case DocumentUpload = 'document_upload';
    case Analysis = 'analysis';
    case TokensConsumed = 'tokens_consumed';
    case StorageBytes = 'storage_bytes';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DocumentUpload => 'Subida de documento',
            self::Analysis => 'Análisis IA',
            self::TokensConsumed => 'Tokens consumidos',
            self::StorageBytes => 'Almacenamiento usado',
        };
    }

    /**
     * Get the unit of measurement for this usage type.
     */
    public function unit(): string
    {
        return match ($this) {
            self::DocumentUpload => 'documentos',
            self::Analysis => 'análisis',
            self::TokensConsumed => 'tokens',
            self::StorageBytes => 'bytes',
        };
    }
}
