<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Documents\AttachSupportingDocumentAction;
use App\DTOs\Documents\AttachSupportingDocumentDTO;
use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AttachSupportingDocumentRequest;
use App\Http\Resources\Api\V1\DocumentAnalysisResource;
use App\Models\AiModel;
use App\Models\AnalysisSupportingDocument;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\User;
use App\Services\Billing\AiCreditService;
use App\Services\LLM\LlmAnalysisService;
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
 *
 * Also manages supporting document attachments to existing analyses,
 * enabling enriched contextual re-analysis with full context preservation.
 */
class DocumentAnalysisController extends Controller
{
    public function __construct(
        private readonly LlmAnalysisService $llmService,
        private readonly AiCreditService $creditService,
        private readonly AttachSupportingDocumentAction $attachAction,
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

        /** @var User $user */
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
            $aiModel = AiModel::query()
                ->active()
                ->whereIn('tier', array_map(fn ($tier) => $tier->value, $plan->allowedTiers()))
                ->ordered()
                ->first() ?? AiModel::defaultModel();
        } else {
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
            $result = $this->llmService->analyze(
                documentContent: $compactContent,
                analysisType: $analysisType,
                document: $document,
                overrideModel: "{$aiModel->provider}::{$aiModel->provider_model_id}",
            );

            $latestVersion = DocumentAnalysis::query()
                ->where('document_id', $document->id)
                ->where('analysis_type', $analysisType)
                ->max('version') ?? 0;

            $inputTokens = (int) $result['input_tokens'];
            $outputTokens = (int) $result['output_tokens'];
            $creditCost = $this->creditService->calculateCreditsCost($inputTokens, $outputTokens, (float) $aiModel->credit_multiplier);

            $analysis = DocumentAnalysis::create([
                'document_id'        => $document->id,
                'organization_id'    => $document->organization_id,
                'requested_by'       => $user->id,
                'analysis_type'      => $analysisType,
                'llm_provider'       => $result['llm_provider'],
                'llm_model'          => $aiModel->slug,
                'content'            => $result['content'],
                'confidence_score'   => $this->extractConfidence($result['content']),
                'input_tokens'       => $inputTokens,
                'output_tokens'      => $outputTokens,
                'processing_time_ms' => $result['processing_time_ms'],
                'prompt_metadata'    => array_merge($result['prompt_metadata'] ?? [], [
                    'model_tier'   => $aiModel->tier->value,
                    'credit_cost'  => $creditCost,
                ]),
                'status'             => DocumentStatus::Processed,
                'version'            => $latestVersion + 1,
            ]);

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

        if ($analysis->document_id !== $document->id) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Análisis no encontrado para este documento.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $analysis->load(['requester:id,name', 'supportingDocuments']);

        return new DocumentAnalysisResource($analysis);
    }

    // ---------------------------------------------------------------
    // Supporting Documents
    // ---------------------------------------------------------------

    /**
     * Attach a supporting document to an existing analysis.
     *
     * POST /api/v1/analyses/{analysis}/supporting-documents
     *
     * The supporting document must belong to the same organization.
     * Enables the AI to incorporate its context into future analyses.
     */
    public function attachDocument(
        AttachSupportingDocumentRequest $request,
        DocumentAnalysis $analysis,
    ): JsonResponse {
        // Authorize via the analysis's primary document
        $this->authorize('view', $analysis->document);

        /** @var User $user */
        $user = $request->user();

        $supportingDocument = Document::query()
            ->forOrganization($user->organization_id)
            ->where('id', $request->input('document_id'))
            ->firstOrFail();

        $dto = AttachSupportingDocumentDTO::fromRequest($request, $analysis->id);

        try {
            $pivot = $this->attachAction->execute($dto, $analysis, $supportingDocument);
        } catch (\LogicException $e) {
            return response()->json([
                'error' => 'attachment_conflict',
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'tenant_violation',
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        // Reload analysis with all supporting documents
        $analysis->load(['requester:id,name', 'supportingDocuments']);

        return response()->json([
            'message' => 'Documento de apoyo adjuntado exitosamente.',
            'pivot_id' => $pivot->id,
            'supporting_document' => [
                'id' => $supportingDocument->id,
                'original_filename' => $supportingDocument->original_filename,
                'status' => $supportingDocument->status->value,
                'is_parsed' => isset($supportingDocument->metadata['parsed_compact_content']),
                'relationship_type' => $pivot->relationship_type,
                'relationship_label' => $pivot->relationshipLabel(),
                'notes' => $pivot->notes,
                'attached_at' => $pivot->created_at?->toIso8601String(),
            ],
            'analysis' => new DocumentAnalysisResource($analysis),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * List all supporting documents attached to an analysis.
     *
     * GET /api/v1/analyses/{analysis}/supporting-documents
     */
    public function listSupportingDocuments(
        Request $request,
        DocumentAnalysis $analysis,
    ): JsonResponse {
        $this->authorize('view', $analysis->document);

        $pivots = AnalysisSupportingDocument::query()
            ->where('analysis_id', $analysis->id)
            ->where('organization_id', $analysis->organization_id)
            ->with(['document:id,original_filename,status,mime_type,metadata', 'attacher:id,name'])
            ->orderBy('created_at')
            ->get();

        $items = $pivots->map(fn (AnalysisSupportingDocument $pivot) => [
            'pivot_id' => $pivot->id,
            'document_id' => $pivot->document_id,
            'original_filename' => $pivot->document?->original_filename,
            'status' => $pivot->document?->status->value,
            'is_parsed' => isset($pivot->document?->metadata['parsed_compact_content']),
            'relationship_type' => $pivot->relationship_type,
            'relationship_label' => $pivot->relationshipLabel(),
            'notes' => $pivot->notes,
            'attached_by' => $pivot->attacher ? [
                'id' => $pivot->attacher->id,
                'name' => $pivot->attacher->name,
            ] : null,
            'attached_at' => $pivot->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'analysis_id' => $analysis->id,
            'count' => $items->count(),
            'supporting_documents' => $items,
        ]);
    }

    /**
     * Detach a supporting document from an analysis.
     *
     * DELETE /api/v1/analyses/{analysis}/supporting-documents/{document}
     */
    public function detachDocument(
        Request $request,
        DocumentAnalysis $analysis,
        Document $document,
    ): JsonResponse {
        $this->authorize('view', $analysis->document);

        /** @var User $user */
        $user = $request->user();

        $deleted = AnalysisSupportingDocument::query()
            ->where('analysis_id', $analysis->id)
            ->where('document_id', $document->id)
            ->where('organization_id', $user->organization_id)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'El documento de apoyo no está adjunto a este análisis.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        Log::info('Supporting document detached from analysis.', [
            'analysis_id' => $analysis->id,
            'document_id' => $document->id,
            'organization_id' => $user->organization_id,
            'detached_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Documento de apoyo desvinculado exitosamente.',
        ]);
    }

    /**
     * Perform a contextual analysis using the existing analysis + all supporting documents.
     *
     * POST /api/v1/analyses/{analysis}/analyze-with-context
     *
     * Builds an enriched prompt that includes:
     * - The original document's parsed content
     * - A summary of the prior analysis
     * - The parsed content of all attached supporting documents
     *
     * Skips unparsed supporting documents (warns in response).
     */
    public function analyzeWithContext(Request $request, DocumentAnalysis $analysis): JsonResponse
    {
        $this->authorize('view', $analysis->document);

        $validated = $request->validate([
            'analysis_type' => [
                'nullable',
                'string',
                Rule::in([AnalysisType::ContextualSummary->value, AnalysisType::FullSummary->value, AnalysisType::RiskAssessment->value]),
            ],
            'model_slug' => ['nullable', 'string', 'exists:ai_models,slug'],
        ], [
            'analysis_type.in' => 'Para análisis contextual, use: contextual_summary, full_summary o risk_assessment.',
        ]);

        $analysisType = AnalysisType::from($validated['analysis_type'] ?? AnalysisType::ContextualSummary->value);

        // Load the primary document
        $primaryDocument = $analysis->document;

        if ($primaryDocument === null) {
            return response()->json([
                'error' => 'document_not_found',
                'message' => 'No se encontró el documento principal del análisis.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // Verify the primary document has been parsed
        $primaryContent = $primaryDocument->metadata['parsed_compact_content'] ?? null;
        if ($primaryContent === null || $primaryContent === '') {
            return response()->json([
                'error' => 'primary_not_parsed',
                'message' => 'El documento principal debe ser procesado antes de realizar un análisis contextual. Use POST /documents/{id}/parse.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = $request->user();
        $organization = $user->organization;

        if ($organization === null) {
            return response()->json([
                'error' => 'no_organization',
                'message' => 'El usuario no pertenece a ninguna organización.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $plan = $organization->currentPlan();

        // Resolve AI model
        $requestedModelSlug = $validated['model_slug'] ?? null;
        if (! $plan->allowsModelSelection() || $requestedModelSlug === null) {
            $aiModel = AiModel::query()
                ->active()
                ->whereIn('tier', array_map(fn ($tier) => $tier->value, $plan->allowedTiers()))
                ->ordered()
                ->first() ?? AiModel::defaultModel();
        } else {
            $aiModel = AiModel::findBySlug($requestedModelSlug);
        }

        // Validate AI Credit Quota
        if (! $this->creditService->canPerformAnalysis($organization, $aiModel)) {
            return response()->json([
                'error' => 'credits_depleted',
                'message' => 'Créditos de Inteligencia Artificial insuficientes. Por favor, actualiza tu plan.',
                'meta' => [
                    'required_credits' => $aiModel->creditCostPerAnalysis(),
                    'model_tier' => $aiModel->tier->value,
                ],
            ], JsonResponse::HTTP_PAYMENT_REQUIRED);
        }

        // Load supporting document models
        $supportingDocuments = $analysis->supportingDocumentFiles()
            ->forOrganization($organization->id)
            ->get();

        if ($supportingDocuments->isEmpty()) {
            return response()->json([
                'error' => 'no_supporting_documents',
                'message' => 'No hay documentos de apoyo adjuntos a este análisis. Use POST /analyses/{id}/supporting-documents para adjuntar documentos.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = $this->llmService->analyzeWithContext(
                primaryDocument: $primaryDocument,
                existingAnalysis: $analysis,
                supportingDocuments: $supportingDocuments,
                analysisType: $analysisType,
                overrideModel: "{$aiModel->provider}::{$aiModel->provider_model_id}",
            );

            $latestVersion = DocumentAnalysis::query()
                ->where('document_id', $primaryDocument->id)
                ->where('analysis_type', $analysisType)
                ->max('version') ?? 0;

            $inputTokens = (int) ($result['input_tokens'] ?? 0);
            $outputTokens = (int) ($result['output_tokens'] ?? 0);
            $creditCost = $this->creditService->calculateCreditsCost($inputTokens, $outputTokens, (float) $aiModel->credit_multiplier);

            $newAnalysis = DocumentAnalysis::create([
                'document_id' => $primaryDocument->id,
                'organization_id' => $analysis->organization_id,
                'requested_by' => $user->id,
                'analysis_type' => $analysisType,
                'llm_provider' => $result['llm_provider'],
                'llm_model' => $aiModel->slug,
                'content' => $result['content'],
                'confidence_score' => $this->extractConfidence($result['content']),
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'processing_time_ms' => $result['processing_time_ms'],
                'prompt_metadata' => array_merge($result['prompt_metadata'] ?? [], [
                    'model_tier' => $aiModel->tier->value,
                    'credit_cost' => $creditCost,
                    'contextual_analysis' => true,
                    'prior_analysis_id' => $analysis->id,
                    'supporting_document_ids' => $supportingDocuments->pluck('id')->all(),
                    'skipped_documents' => $result['skipped_documents'] ?? [],
                ]),
                'status' => DocumentStatus::Processed,
                'version' => $latestVersion + 1,
            ]);

            $this->creditService->deductCredits($organization, $user, $aiModel, $newAnalysis);

            $newAnalysis->load('requester:id,name');

            $response = (new DocumentAnalysisResource($newAnalysis))->toArray($request);
            $response['skipped_documents'] = $result['skipped_documents'] ?? [];

            return response()->json(
                array_merge(['message' => 'Análisis contextual completado exitosamente.'], $response),
                JsonResponse::HTTP_CREATED,
            );
        } catch (\RuntimeException $exception) {
            Log::error('Contextual analysis failed via controller.', [
                'analysis_id' => $analysis->id,
                'document_id' => $primaryDocument->id,
                'organization_id' => $analysis->organization_id,
                'analysis_type' => $analysisType->value,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'analysis_failed',
                'message' => 'No se pudo completar el análisis contextual. Intente nuevamente.',
            ], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    /**
     * Extract confidence level from LLM response content.
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
