<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Documents\StoreDocumentAction;
use App\DTOs\Documents\StoreDocumentDTO;
use App\Enums\DocumentStatus;
use App\Models\AiModel;
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

    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $metrics = $this->metricsService->forOrganization($user);

        $selectedModelSlug = (string) session('selected_model_slug', '');
        if ($selectedModelSlug === '' || ! AiModel::query()->where('slug', $selectedModelSlug)->where('is_active', true)->exists()) {
            $defaultSlug = $user->hasProAccess() ? 'cerebras-gemma4' : 'llama-8b-instant';
            $selectedModelSlug = AiModel::query()->where('slug', $defaultSlug)->where('is_active', true)->exists()
                ? $defaultSlug
                : (AiModel::query()->active()->ordered()->first()?->slug ?? 'cerebras-gemma4');

            session(['selected_model_slug' => $selectedModelSlug]);
        }

        $selectedModel = AiModel::query()->where('slug', $selectedModelSlug)->first();
        $metrics['selectedModel'] = $selectedModel;
        $metrics['selectedModelSlug'] = $selectedModelSlug;
        $metrics['currentProvider'] = $selectedModel?->provider ?? (string) session('llm_provider', config('llm.default_provider', 'cerebras'));

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
     * Switch active AI Model for the current session.
     */
    public function setModel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'model_slug' => ['required', 'string', 'exists:ai_models,slug'],
        ]);

        $aiModel = AiModel::findBySlug($validated['model_slug']);
        session([
            'selected_model_slug' => $aiModel->slug,
            'llm_provider' => $aiModel->provider,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', "Modelo de Inteligencia Artificial seleccionado: {$aiModel->display_name}.");
    }

    /**
     * Switch active LLM provider for the current session (legacy support).
     */
    public function setProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:groq,nvidia_nim,cerebras,qwen_cloud'],
        ]);

        $aiModel = AiModel::query()
            ->where('provider', $validated['provider'])
            ->where('is_active', true)
            ->ordered()
            ->first();

        session([
            'llm_provider' => $validated['provider'],
            'selected_model_slug' => $aiModel?->slug,
        ]);

        $label = match ($validated['provider']) {
            'cerebras' => 'Cerebras AI',
            'nvidia_nim' => 'NVIDIA NIM',
            'qwen_cloud' => 'Qwen Cloud',
            default => 'Groq Cloud',
        };

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
            'model_slug' => ['nullable', 'string', 'exists:ai_models,slug'],
            'llm_provider' => ['nullable', 'string'],
        ], [
            'file.required' => 'Debe adjuntar un archivo.',
            'file.mimetypes' => 'Solo se permiten archivos PDF y DOCX.',
            'file.max' => 'El archivo excede el tamaño máximo permitido (50 MB).',
        ]);

        $requestedModelSlug = $validated['model_slug'] ?? session('selected_model_slug');
        $aiModel = null;

        if ($requestedModelSlug) {
            $aiModel = AiModel::query()->where('slug', $requestedModelSlug)->where('is_active', true)->first();
        }

        if ($aiModel === null && ! empty($validated['llm_provider'])) {
            $aiModel = AiModel::query()->where('provider', $validated['llm_provider'])->where('is_active', true)->ordered()->first();
        }

        if ($aiModel === null) {
            $defaultSlug = $user->hasProAccess() ? 'cerebras-gemma4' : 'llama-8b-instant';
            $aiModel = AiModel::query()->where('slug', $defaultSlug)->where('is_active', true)->first()
                ?? AiModel::query()->where('is_active', true)->ordered()->first();
        }

        $selectedProvider = $aiModel?->provider ?? $validated['llm_provider'] ?? session('llm_provider', config('llm.default_provider', 'cerebras'));
        $selectedModelSlug = $aiModel?->slug;

        session([
            'selected_model_slug' => $selectedModelSlug,
            'llm_provider' => $selectedProvider,
        ]);

        try {
            $dto = new StoreDocumentDTO(
                file: $request->file('file'),
                organizationId: (int) $user->organization_id,
                uploadedBy: (int) $user->id,
                metadata: [
                    'llm_provider' => $selectedProvider,
                    'model_slug' => $selectedModelSlug,
                    'provider_model_id' => $aiModel?->provider_model_id,
                ],
            );

            $this->storeAction->execute(
                dto: $dto,
                ipAddress: $request->ip(),
                processImmediately: true,
            );

            $modelDisplayName = $aiModel?->display_name ?? ($selectedProvider === 'cerebras' ? 'Cerebras AI' : $selectedProvider);
            $message = $user->hasProAccess()
                ? "Documento subido. Procesamiento prioritario iniciado con {$modelDisplayName}."
                : "Documento subido correctamente. El análisis comenzará en unos segundos con {$modelDisplayName}.";

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
