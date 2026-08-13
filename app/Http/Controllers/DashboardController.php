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
        $metrics['currentProvider'] = (string) session('llm_provider', config('llm.default_provider', 'groq'));

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
     * Switch active LLM provider for the current session.
     */
    public function setProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:groq,nvidia_nim'],
        ]);

        session(['llm_provider' => $validated['provider']]);

        $label = $validated['provider'] === 'nvidia_nim' ? 'NVIDIA NIM' : 'Groq Cloud';

        return redirect()
            ->route('dashboard')
            ->with('success', "Proveedor de Inteligencia Artificial cambiado a {$label}.");
    }

    /**
     * Handle document upload from the web interface.
     */
    public function upload(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'llm_provider' => ['nullable', 'string', 'in:groq,nvidia_nim'],
        ], [
            'file.required' => 'Debe adjuntar un archivo.',
            'file.mimetypes' => 'Solo se permiten archivos PDF y DOCX.',
            'file.max' => 'El archivo excede el tamaño máximo permitido (50 MB).',
        ]);

        $selectedProvider = $validated['llm_provider']
            ?? session('llm_provider', config('llm.default_provider', 'groq'));

        session(['llm_provider' => $selectedProvider]);

        try {
            $dto = new StoreDocumentDTO(
                file: $request->file('file'),
                organizationId: (int) $user->organization_id,
                uploadedBy: (int) $user->id,
                metadata: ['llm_provider' => $selectedProvider],
            );

            $this->storeAction->execute(
                dto: $dto,
                ipAddress: $request->ip(),
                processImmediately: true,
            );

            $providerName = $selectedProvider === 'nvidia_nim' ? 'NVIDIA NIM' : 'Groq';
            $message = $user->hasProAccess()
                ? "Documento subido. Procesamiento prioritario iniciado con {$providerName}."
                : "Documento subido correctamente. El análisis comenzará en unos segundos con {$providerName}.";

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

    /**
     * Delete a document and all its associated analyses in cascade.
     */
    public function destroy(
        Request $request,
        Document $document,
        \App\Actions\Documents\DeleteDocumentAction $deleteAction
    ): RedirectResponse {
        /** @var User $user */
        $user = Auth::user();

        if ($document->organization_id !== $user->organization_id) {
            abort(403, 'No tienes permisos para eliminar este documento.');
        }

        $filename = $document->original_filename;

        try {
            $deleteAction->execute($document);

            return redirect()
                ->route('dashboard')
                ->with('success', "El documento \"{$filename}\" y todos sus análisis asociados fueron eliminados correctamente.");
        } catch (\Throwable $exception) {
            Log::error('Document deletion failed.', [
                'document_id' => $document->id,
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('dashboard')
                ->with('error', 'Error al eliminar el documento: ' . $exception->getMessage());
        }
    }
}
