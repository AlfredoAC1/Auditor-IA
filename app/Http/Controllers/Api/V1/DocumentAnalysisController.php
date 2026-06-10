<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DocumentAnalysisResource;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\LLM\GroqAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Controller for document analysis via LLM (Groq).
 *
 * Receives a document ID and analysis type, sends the parsed
 * content to Groq, and stores the structured analysis result.
 *
 * This controller only coordinates — analysis logic lives in
 * GroqAnalysisService. Authorization via DocumentPolicy.
 */
class DocumentAnalysisController extends Controller
{
    public function __construct(
        private readonly GroqAnalysisService $groqService,
    ) {}

    /**
     * Request a new analysis for a document.
     *
     * POST /api/v1/documents/{document}/analyze
     *
     * Requires the document to be parsed first (via DocumentParsingController).
     */
    public function analyze(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $validated = $request->validate([
            'analysis_type' => ['required', 'string', Rule::enum(AnalysisType::class)],
        ], [
            'analysis_type.required' => 'Debe especificar el tipo de análisis.',
            'analysis_type.enum' => 'El tipo de análisis no es válido.',
        ]);

        $analysisType = AnalysisType::from($validated['analysis_type']);

        // Verify document has been parsed
        $metadata = $document->metadata ?? [];
        $compactContent = $metadata['parsed_compact_content'] ?? null;

        if ($compactContent === null || $compactContent === '') {
            return response()->json([
                'error' => 'not_parsed',
                'message' => 'El documento debe ser procesado primero. Use POST /documents/{id}/parse.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        try {
            $result = $this->groqService->analyze(
                documentContent: $compactContent,
                analysisType: $analysisType,
                document: $document,
            );

            // Determine version (increment if same type already exists)
            $latestVersion = DocumentAnalysis::query()
                ->where('document_id', $document->id)
                ->where('analysis_type', $analysisType)
                ->max('version') ?? 0;

            $analysis = DocumentAnalysis::create([
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'requested_by' => $user->id,
                'analysis_type' => $analysisType,
                'llm_provider' => $result['llm_provider'],
                'llm_model' => $result['llm_model'],
                'content' => $result['content'],
                'confidence_score' => $this->extractConfidence($result['content']),
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'processing_time_ms' => $result['processing_time_ms'],
                'prompt_metadata' => $result['prompt_metadata'],
                'status' => DocumentStatus::Processed,
                'version' => $latestVersion + 1,
            ]);

            $analysis->load('requester:id,name');

            return (new DocumentAnalysisResource($analysis))
                ->response()
                ->setStatusCode(JsonResponse::HTTP_CREATED);
        } catch (\RuntimeException $exception) {
            Log::error('Document analysis failed via controller.', [
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'analysis_type' => $analysisType->value,
                'error' => $exception->getMessage(),
            ]);

            // Store failed analysis for audit trail
            DocumentAnalysis::create([
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'requested_by' => $user->id,
                'analysis_type' => $analysisType,
                'llm_provider' => 'groq',
                'llm_model' => (string) config('llm.providers.groq.model', 'unknown'),
                'content' => ['error' => $exception->getMessage()],
                'status' => DocumentStatus::Failed,
                'version' => 1,
            ]);

            return response()->json([
                'error' => 'analysis_failed',
                'message' => 'No se pudo completar el análisis. Intente nuevamente.',
            ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * List all analyses for a document.
     *
     * GET /api/v1/documents/{document}/analyses
     */
    public function index(Request $request, Document $document): AnonymousResourceCollection
    {
        $this->authorize('view', $document);

        $analyses = DocumentAnalysis::query()
            ->where('document_id', $document->id)
            ->where('organization_id', $document->organization_id)
            ->with('requester:id,name')
            ->when(
                $request->filled('analysis_type'),
                fn ($query) => $query->where('analysis_type', $request->input('analysis_type')),
            )
            ->orderByDesc('created_at')
            ->paginate(
                min((int) $request->input('per_page', 15), 50)
            );

        return DocumentAnalysisResource::collection($analyses);
    }

    /**
     * Show a specific analysis.
     *
     * GET /api/v1/documents/{document}/analyses/{analysis}
     */
    public function show(Document $document, DocumentAnalysis $analysis): DocumentAnalysisResource|JsonResponse
    {
        $this->authorize('view', $document);

        // Verify analysis belongs to this document (defense in depth)
        if ($analysis->document_id !== $document->id) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Análisis no encontrado para este documento.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $analysis->load('requester:id,name');

        return new DocumentAnalysisResource($analysis);
    }

    /**
     * Extract confidence level from LLM response content.
     *
     * Maps the string confidence level to a numeric score.
     */
    private function extractConfidence(array $content): ?float
    {
        $level = $content['nivel_confianza'] ?? null;

        return match ($level) {
            'alto' => 0.85,
            'medio' => 0.60,
            'bajo' => 0.35,
            default => null,
        };
    }
}
