<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DocumentAnalysisResource;
use App\Models\AiModel;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\Billing\AiCreditService;
use App\Services\LLM\GroqAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Controller for document analysis via LLM.
 *
 * Coordinates model selection, AI credit quota validation,
 * document analysis execution, and credit deduction.
 */
class DocumentAnalysisController extends Controller
{
    public function __construct(
        private readonly GroqAnalysisService $groqService,
        private readonly AiCreditService $creditService,
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
            'model_slug' => ['nullable', 'string', 'exists:ai_models,slug'],
        ], [
            'analysis_type.required' => 'Debe especificar el tipo de análisis.',
            'analysis_type.enum' => 'El tipo de análisis no es válido.',
            'model_slug.exists' => 'El modelo seleccionado no está disponible.',
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
        $organization = $user->organization;

        if ($organization === null) {
            return response()->json([
                'error' => 'no_organization',
                'message' => 'El usuario no pertenece a ninguna organización.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $plan = $organization->currentPlan();

        // 1. Resolve AI Model to use
        $requestedModelSlug = $validated['model_slug'] ?? null;

        if (! $plan->allowsModelSelection() || $requestedModelSlug === null) {
            // Free users or unselected: default model for their tier
            $aiModel = AiModel::query()
                ->active()
                ->whereIn('tier', array_map(fn ($tier) => $tier->value, $plan->allowedTiers()))
                ->ordered()
                ->first() ?? AiModel::defaultModel();
        } else {
            // Pro / Lifetime users choosing a model
            $aiModel = AiModel::findBySlug($requestedModelSlug);
        }

        // 2. Validate AI Credit Quota
        if (! $this->creditService->canPerformAnalysis($organization, $aiModel)) {
            return response()->json([
                'error' => 'credits_depleted',
                'message' => 'Créditos de Inteligencia Artificial insuficientes para realizar este análisis. Por favor, actualiza tu plan para obtener más créditos.',
                'meta' => [
                    'required_credits' => $aiModel->creditCostPerAnalysis(),
                    'model_tier' => $aiModel->tier->value,
                ],
            ], JsonResponse::HTTP_PAYMENT_REQUIRED);
        }

        try {
            $result = $this->groqService->analyze(
                documentContent: $compactContent,
                analysisType: $analysisType,
                document: $document,
                overrideModel: $aiModel->provider_model_id,
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
                'llm_model' => $aiModel->slug,
                'content' => $result['content'],
                'confidence_score' => $this->extractConfidence($result['content']),
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'processing_time_ms' => $result['processing_time_ms'],
                'prompt_metadata' => array_merge($result['prompt_metadata'] ?? [], [
                    'model_tier' => $aiModel->tier->value,
                    'credit_cost' => $aiModel->creditCostPerAnalysis(),
                ]),
                'status' => DocumentStatus::Processed,
                'version' => $latestVersion + 1,
            ]);

            // Deduct AI credits for completed analysis
            $this->creditService->deductCredits($organization, $user, $aiModel, $analysis);

            $analysis->load('requester:id,name');

            return (new DocumentAnalysisResource($analysis))
                ->response()
                ->setStatusCode(JsonResponse::HTTP_CREATED);
        } catch (\RuntimeException $exception) {
            Log::error('Document analysis failed via controller.', [
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'analysis_type' => $analysisType->value,
                'model_slug' => $aiModel->slug,
                'error' => $exception->getMessage(),
            ]);

            // Store failed analysis for audit trail
            $failedAnalysis = DocumentAnalysis::create([
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'requested_by' => $user->id,
                'analysis_type' => $analysisType,
                'llm_provider' => $aiModel->provider,
                'llm_model' => $aiModel->slug,
                'content' => ['error' => $exception->getMessage()],
                'status' => DocumentStatus::Failed,
                'version' => 1,
            ]);

            // If credits were depleted during error handling, purge failed record if needed
            $ledger = $organization->getOrCreateCreditLedger();
            if ($plan->is_free && $ledger->isDepleted()) {
                $this->creditService->purgeDepletedAnalysis($failedAnalysis, 'free_tier_credits_depleted');
            }

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
