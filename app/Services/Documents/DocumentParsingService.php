<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service responsible for parsing documents via Unstructured API.
 *
 * Sends the stored document file to the Unstructured service,
 * which returns a structured JSON representation of the content.
 * This JSON is significantly more token-efficient than raw text
 * for LLM analysis.
 *
 * Does NOT handle database updates — the caller decides what to
 * persist and when.
 */
final class DocumentParsingService
{
    private readonly string $apiUrl;

    private readonly string $apiKey;

    private readonly int $timeoutSeconds;

    public function __construct()
    {
        $this->apiUrl = (string) config('documents.unstructured.api_url', 'http://localhost:8000/general/v0/general');
        $this->apiKey = (string) config('documents.unstructured.api_key', '');
        $this->timeoutSeconds = (int) config('documents.unstructured.timeout_seconds', 120);
    }

    /**
     * Parse a document into structured JSON via Unstructured API.
     *
     * @return array{
     *     elements: array<int, array{
     *         type: string,
     *         element_id: string,
     *         text: string,
     *         metadata: array<string, mixed>,
     *     }>,
     *     element_count: int,
     *     estimated_tokens: int,
     * }
     *
     * @throws \RuntimeException If parsing fails
     */
    public function parse(Document $document): array
    {
        $filePath = $document->fullStoragePath();
        $disk = $document->disk;

        if (! Storage::disk($disk)->exists($filePath)) {
            throw new \RuntimeException(
                "Document file not found on disk [{$disk}] at [{$filePath}]."
            );
        }

        $startTime = microtime(true);

        $fileContent = Storage::disk($disk)->get($filePath);

        if ($fileContent === null) {
            throw new \RuntimeException(
                "Failed to read document file [{$filePath}] from disk [{$disk}]."
            );
        }

        Log::info('Document parsing started.', [
            'document_id' => $document->id,
            'organization_id' => $document->organization_id,
            'mime_type' => $document->mime_type,
            'file_size_bytes' => $document->file_size_bytes,
        ]);

        $response = $this->callUnstructuredApi(
            fileContent: $fileContent,
            filename: $document->stored_filename,
            mimeType: $document->mime_type,
        );

        $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        $elements = $this->normalizeElements($response);
        $estimatedTokens = $this->estimateTokenCount($elements);

        Log::info('Document parsing completed.', [
            'document_id' => $document->id,
            'organization_id' => $document->organization_id,
            'element_count' => count($elements),
            'estimated_tokens' => $estimatedTokens,
            'processing_time_ms' => $processingTimeMs,
        ]);

        return [
            'elements' => $elements,
            'element_count' => count($elements),
            'estimated_tokens' => $estimatedTokens,
        ];
    }

    /**
     * Build a compact text representation from parsed elements.
     *
     * Concatenates only the text content, grouped by type,
     * optimized for LLM consumption (minimal tokens, maximum context).
     */
    public function buildCompactContent(array $elements): string
    {
        $sections = [];

        foreach ($elements as $element) {
            $type = $element['type'] ?? 'Unknown';
            $text = trim($element['text'] ?? '');

            if ($text === '') {
                continue;
            }

            // Group by element type for structured output
            if (! isset($sections[$type])) {
                $sections[$type] = [];
            }

            $sections[$type][] = $text;
        }

        $output = [];

        foreach ($sections as $type => $texts) {
            $output[] = "--- {$type} ---";

            foreach ($texts as $text) {
                $output[] = $text;
            }

            $output[] = '';
        }

        return implode("\n", $output);
    }

    /**
     * Convert parsing result array directly to a compact string.
     *
     * @param  array{elements?: array<int, array<string, mixed>>}|array<int, array<string, mixed>>  $parseResult
     */
    public function toCompactString(array $parseResult): string
    {
        $elements = isset($parseResult['elements']) && is_array($parseResult['elements'])
            ? $parseResult['elements']
            : $parseResult;

        return $this->buildCompactContent($elements);
    }

    /**
     * Call the Unstructured API with the document file.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws \RuntimeException On API failure
     */
    private function callUnstructuredApi(string $fileContent, string $filename, string $mimeType): array
    {
        $headers = [];

        if ($this->apiKey !== '') {
            $headers['unstructured-api-key'] = $this->apiKey;
        }

        $response = Http::timeout($this->timeoutSeconds)
            ->withHeaders($headers)
            ->attach('files', $fileContent, $filename, ['Content-Type' => $mimeType])
            ->post($this->apiUrl, [
                'strategy' => 'auto',
                'languages' => ['spa'],
                'output_format' => 'application/json',
            ]);

        if ($response->failed()) {
            $errorBody = $response->body();

            Log::error('Unstructured API call failed.', [
                'status' => $response->status(),
                'error' => mb_substr($errorBody, 0, 500),
            ]);

            throw new \RuntimeException(
                "Unstructured API returned status [{$response->status()}]: ".mb_substr($errorBody, 0, 200)
            );
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new \RuntimeException('Unstructured API returned invalid JSON response.');
        }

        return $data;
    }

    /**
     * Normalize raw Unstructured API elements into a consistent format.
     *
     * @param  array<int, array<string, mixed>>  $rawElements
     * @return array<int, array{type: string, element_id: string, text: string, metadata: array<string, mixed>}>
     */
    private function normalizeElements(array $rawElements): array
    {
        return array_values(array_filter(array_map(function (array $element): ?array {
            $text = trim((string) ($element['text'] ?? ''));

            // Skip empty elements
            if ($text === '') {
                return null;
            }

            return [
                'type' => (string) ($element['type'] ?? 'NarrativeText'),
                'element_id' => (string) ($element['element_id'] ?? ''),
                'text' => $text,
                'metadata' => array_intersect_key(
                    (array) ($element['metadata'] ?? []),
                    array_flip(['page_number', 'languages', 'parent_id', 'category_depth']),
                ),
            ];
        }, $rawElements)));
    }

    /**
     * Estimate the token count from parsed elements.
     *
     * Rough estimation: ~4 characters per token (conservative for Spanish).
     */
    private function estimateTokenCount(array $elements): int
    {
        $totalChars = 0;

        foreach ($elements as $element) {
            $totalChars += mb_strlen($element['text'] ?? '');
        }

        return (int) ceil($totalChars / 4);
    }
}
