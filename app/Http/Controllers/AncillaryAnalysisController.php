<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Documents\StoreAndDispatchAncillaryAction;
use App\DTOs\Documents\UploadAncillaryDocumentDTO;
use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Http\Requests\UploadAncillaryDocumentRequest;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for handling Ancillary (Supporting) Document attachments,
 * status polling, and displaying the new contextual multi-document analysis view.
 */
class AncillaryAnalysisController extends Controller
{
    public function __construct(
        private readonly StoreAndDispatchAncillaryAction $storeAction,
    ) {}

    /**
     * Upload an ancillary document and dispatch contextual analysis.
     *
     * POST /analisis/{analysis}/ancillary
     */
    public function upload(
        UploadAncillaryDocumentRequest $request,
        DocumentAnalysis $analysis,
    ): JsonResponse {
        $this->authorize('addAncillary', $analysis);

        $dto = UploadAncillaryDocumentDTO::fromRequest(
            request: $request,
            analysisId: $analysis->id,
            primaryDocumentId: $analysis->document_id,
        );

        try {
            $result = $this->storeAction->execute($dto, $analysis);

            return response()->json([
                'message'               => 'Documento anexo recibido. Iniciando procesamiento contextual...',
                'ancillary_document_id' => $result['ancillary_document']->id,
                'status_url'            => route('analysis.ancillary.status', [
                    'analysis'          => $analysis->id,
                    'ancillaryDocument' => $result['ancillary_document']->id,
                ]),
            ], Response::HTTP_ACCEPTED);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error'   => 'processing_error',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Poll processing status for an ancillary document contextual analysis.
     *
     * GET /analisis/{analysis}/ancillary/{ancillaryDocument}/status
     */
    public function status(
        Request $request,
        DocumentAnalysis $analysis,
        Document $ancillaryDocument,
    ): JsonResponse {
        $this->authorize('view', $analysis);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($ancillaryDocument->organization_id !== $user->organization_id) {
            abort(403);
        }

        $ancillaryDocument->refresh();

        $contextualAnalysisId = $ancillaryDocument->metadata['contextual_analysis_id'] ?? null;
        $contextualAnalysis = null;

        if ($contextualAnalysisId !== null) {
            $contextualAnalysis = DocumentAnalysis::query()
                ->where('id', $contextualAnalysisId)
                ->where('organization_id', $user->organization_id)
                ->first();
        }

        if ($contextualAnalysis === null && $ancillaryDocument->status === DocumentStatus::Processed) {
            $contextualAnalysis = DocumentAnalysis::query()
                ->where('document_id', $analysis->document_id)
                ->where('analysis_type', AnalysisType::ContextualSummary)
                ->where('status', DocumentStatus::Processed)
                ->orderByDesc('created_at')
                ->first();
        }

        $isTerminal = in_array(
            $ancillaryDocument->status,
            [DocumentStatus::Processed, DocumentStatus::Failed],
            true,
        );

        $contextualUrl = null;
        if ($contextualAnalysis !== null) {
            $contextualUrl = route('analysis.contextual.show', [
                'document' => $analysis->document_id,
                'analysis' => $contextualAnalysis->id,
            ]);
        }

        return response()->json([
            'id'             => $ancillaryDocument->id,
            'status'         => $ancillaryDocument->status->value,
            'status_label'   => $ancillaryDocument->status->label(),
            'is_terminal'    => $isTerminal,
            'analysis_url'   => $contextualUrl,
            'error'          => $ancillaryDocument->metadata['last_error'] ?? null,
        ]);
    }

    /**
     * Show the contextual multi-document analysis view featuring
     * complementary data, cross-document discrepancies, and bonus legal insights.
     *
     * GET /analisis/{document}/contextual/{analysis}
     */
    public function showContextual(
        Request $request,
        Document $document,
        DocumentAnalysis $analysis,
    ): View {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($document->organization_id !== $user->organization_id || $analysis->organization_id !== $user->organization_id) {
            abort(403, 'No tienes permiso para ver este análisis.');
        }

        if ($analysis->document_id !== $document->id) {
            abort(404, 'El análisis no corresponde al documento principal especificado.');
        }

        $analysis->load(['supportingDocuments.document', 'requester']);

        // Also fetch the primary standalone analysis for reference/comparison
        $primaryAnalysis = DocumentAnalysis::query()
            ->where('document_id', $document->id)
            ->where('analysis_type', '!=', AnalysisType::ContextualSummary)
            ->where('status', DocumentStatus::Processed)
            ->orderByDesc('created_at')
            ->first();

        return view('analysis-contextual', compact('document', 'analysis', 'primaryAnalysis'));
    }
}
