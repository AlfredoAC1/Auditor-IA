<?php

declare(strict_types=1);

namespace App\Services\Documents;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service responsible for storing document files on disk.
 *
 * Handles path generation, file naming, and tenant isolation
 * at the filesystem level. Does NOT handle database operations.
 */
final class DocumentStorageService
{
    /**
     * Store a document file in the tenant-isolated storage path.
     *
     * @return array{
     *     stored_filename: string,
     *     storage_path: string,
     *     disk: string,
     *     file_hash: string,
     *     mime_type: string,
     *     file_size_bytes: int,
     * }
     *
     * @throws \RuntimeException If file storage fails
     */
    public function store(UploadedFile $file, int $organizationId): array
    {
        $disk = (string) config('documents.storage.disk', 'local');
        $basePath = (string) config('documents.storage.base_path', 'documents');

        $storagePath = $this->generateTenantPath($basePath, $organizationId);
        $storedFilename = $this->generateSafeFilename($file);

        $fullPath = $storagePath . '/' . $storedFilename;

        // Store file on the configured disk
        $result = Storage::disk($disk)->putFileAs(
            $storagePath,
            $file,
            $storedFilename,
        );

        if ($result === false) {
            throw new \RuntimeException(
                "Failed to store document for organization [{$organizationId}]."
            );
        }

        return [
            'stored_filename' => $storedFilename,
            'storage_path' => $storagePath,
            'disk' => $disk,
            'file_hash' => $this->computeFileHash($file),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size_bytes' => (int) $file->getSize(),
        ];
    }

    /**
     * Delete a document file from disk.
     *
     * @throws \RuntimeException If deletion fails
     */
    public function delete(string $storagePath, string $storedFilename, string $disk): bool
    {
        $fullPath = $storagePath . '/' . $storedFilename;

        return Storage::disk($disk)->delete($fullPath);
    }

    /**
     * Check if a document file exists on disk.
     */
    public function exists(string $storagePath, string $storedFilename, string $disk): bool
    {
        return Storage::disk($disk)->exists($storagePath . '/' . $storedFilename);
    }

    /**
     * Generate a tenant-isolated storage path.
     *
     * Format: documents/{organization_id}/{year}/{month}
     * This provides physical isolation per tenant and date-based partitioning.
     */
    private function generateTenantPath(string $basePath, int $organizationId): string
    {
        $now = now();

        return sprintf(
            '%s/%d/%s/%s',
            $basePath,
            $organizationId,
            $now->format('Y'),
            $now->format('m'),
        );
    }

    /**
     * Generate a safe, non-guessable filename.
     *
     * Uses UUID to prevent path traversal and filename enumeration.
     * Preserves the original extension for downstream processing.
     */
    private function generateSafeFilename(UploadedFile $file): string
    {
        $extension = $this->getSafeExtension($file);

        return Str::uuid()->toString() . '.' . $extension;
    }

    /**
     * Get a validated file extension.
     *
     * Falls back to guessing from MIME type rather than trusting
     * the client-provided extension.
     */
    private function getSafeExtension(UploadedFile $file): string
    {
        $allowedExtensions = (array) config('documents.upload.allowed_extensions', ['pdf', 'docx']);

        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $allowedExtensions, true)) {
            return $extension;
        }

        // Fallback: guess from MIME type
        return $file->guessExtension() ?? 'bin';
    }

    /**
     * Compute SHA-256 hash for file integrity verification.
     */
    private function computeFileHash(UploadedFile $file): string
    {
        $path = $file->getRealPath();

        if ($path === false) {
            throw new \RuntimeException('Cannot compute hash: file path unavailable.');
        }

        return hash_file('sha256', $path);
    }
}
