<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Documents\DocumentParsingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller for document parsing operations.
 *
 * Receives a document ID, sends the file to Unstructured API,
 * and returns/stores the structured JSON representation.
 *
 * This controller only coordinates — parsing logic lives in
 * DocumentParsingService. Authorization via DocumentPolicy.
 */
class DocumentParsingController extends Controller
{
    public function __construct(
        private readonly DocumentParsingService $parsingService,
    ) {}

    /**
     * Parse a document into structured JSON via Unstructured.
     *
     * POST /api/v1/documents/{document}/parse
     *
     * Processes the stored document file and saves the parsed
     * JSON into the document's metadata. Subsequent calls will
     * re-parse (useful if Unstructured version improves).
     */
    public function parse(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        // Check document has a stored file
        if ($document->storage_path === null || $document->stored_filename === null) {
            return response()->json([
                'error' => 'document_not_stored',
                'message' => 'El documento no tiene un archivo almacenado para procesar.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = $this->parsingService->parse($document);

            $compactContent = $this->parsingService->buildCompactContent($result['elements']);

            // Store parsed data in document metadata
            $document->update([
                'metadata' => array_merge($document->metadata ?? [], [
                    'parsed_elements' => $result['elements'],
                    'parsed_element_count' => $result['element_count'],
                    'parsed_estimated_tokens' => $result['estimated_tokens'],
                    'parsed_compact_content' => $compactContent,
                    'parsed_at' => now()->toIso8601String(),
                ]),
            ]);

            return response()->json([
                'message' => 'Documento procesado exitosamente.',
                'document_id' => $document->id,
                'element_count' => $result['element_count'],
                'estimated_tokens' => $result['estimated_tokens'],
                'compact_content_preview' => mb_substr($compactContent, 0, 500).'...',
            ]);
        } catch (\RuntimeException $exception) {
            Log::error('Document parsing failed via controller.', [
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'parsing_failed',
                'message' => 'No se pudo procesar el documento. Intente nuevamente.',
            ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Show the parsed content of a document.
     *
     * GET /api/v1/documents/{document}/parsed
     *
     * Returns the previously parsed JSON data without re-processing.
     */
    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $metadata = $document->metadata ?? [];

        if (! isset($metadata['parsed_elements'])) {
            return response()->json([
                'error' => 'not_parsed',
                'message' => 'El documento aún no ha sido procesado. Use POST /documents/{id}/parse primero.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json([
            'document_id' => $document->id,
            'element_count' => $metadata['parsed_element_count'] ?? 0,
            'estimated_tokens' => $metadata['parsed_estimated_tokens'] ?? 0,
            'parsed_at' => $metadata['parsed_at'] ?? null,
            'compact_content' => $metadata['parsed_compact_content'] ?? '',
            'elements' => $metadata['parsed_elements'] ?? [],
        ]);
    }
}
