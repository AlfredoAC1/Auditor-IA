<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Documents\StoreDocumentAction;
use App\DTOs\Documents\StoreDocumentDTO;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\User;
use App\Services\Dashboard\DashboardMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StoreDocumentAction $storeAction,
        private readonly DashboardMetricsService $metricsService,
    ) {}

    /**
     * Show the dashboard with document list and metrics.
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $metrics = $this->metricsService->forOrganization($user);

        $view = $user->hasProAccess() ? 'dashboard-pro' : 'dashboard';

        return view($view, $metrics);
    }

    /**
     * Poll document processing status (used by dashboard live updates).
     */
    public function documentStatus(Request $request, Document $document): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($document->organization_id !== $user->organization_id) {
            abort(403);
        }

        $document->refresh();

        $analysis = DocumentAnalysis::query()
            ->where('document_id', $document->id)
            ->where('status', DocumentStatus::Processed)
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'id' => $document->id,
            'status' => $document->status->value,
            'status_label' => $document->status->label(),
            'is_terminal' => in_array($document->status, [DocumentStatus::Processed, DocumentStatus::Failed], true),
            'analysis_url' => $analysis !== null ? route('analysis.show', $document) : null,
            'error' => $document->metadata['last_error'] ?? null,
        ]);
    }

    /**
     * Show detailed analysis of a document.
     */
    public function show(Document $document): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($document->organization_id !== $user->organization_id) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $analysis = DocumentAnalysis::query()
            ->where('document_id', $document->id)
            ->where('status', DocumentStatus::Processed)
            ->orderByDesc('created_at')
            ->first();

        return view('analysis-detail', compact('document', 'analysis'));
    }

    /**
     * Show clause analysis of a document.
     */
    public function showClauses(Document $document): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($document->organization_id !== $user->organization_id) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $analysis = DocumentAnalysis::query()
            ->where('document_id', $document->id)
            ->where('status', DocumentStatus::Processed)
            ->orderByDesc('created_at')
            ->first();

        return view('analysis-clauses', compact('document', 'analysis'));
    }

    /**
     * Handle document upload from the web interface.
     */
    public function upload(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ], [
            'file.required' => 'Debe adjuntar un archivo.',
            'file.mimetypes' => 'Solo se permiten archivos PDF y DOCX.',
            'file.max' => 'El archivo excede el tamaño máximo permitido (50 MB).',
        ]);

        try {
            $dto = new StoreDocumentDTO(
                file: $request->file('file'),
                organizationId: (int) $user->organization_id,
                uploadedBy: (int) $user->id,
            );

            $this->storeAction->execute(
                dto: $dto,
                ipAddress: $request->ip(),
                processImmediately: true,
            );

            $message = $user->hasProAccess()
                ? 'Documento subido. Procesamiento prioritario iniciado — actualizaremos el estado en segundos.'
                : 'Documento subido correctamente. El análisis comenzará en unos segundos.';

            return redirect()
                ->route('dashboard')
                ->with('success', $message);
        } catch (\Throwable $exception) {
            Log::error('Web document upload failed.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('dashboard')
                ->with('error', 'Error al procesar el archivo: ' . $exception->getMessage());
        }
    }
}
