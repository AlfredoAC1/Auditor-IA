<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Documents\StoreDocumentAction;
use App\DTOs\Documents\StoreDocumentDTO;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StoreDocumentAction $storeAction,
    ) {}

    /**
     * Ensure a user is authenticated (convenience for development/testing).
     */
    private function ensureAuthenticatedUser(): User
    {
        if (! Auth::check()) {
            $user = User::first();
            if (! $user) {
                $org = Organization::create([
                    'name' => 'Demo Org',
                    'slug' => 'demo-org',
                    'is_active' => true,
                ]);
                $user = User::create([
                    'name' => 'Alfredo Águila',
                    'email' => 'alfredo@example.com',
                    'password' => bcrypt('password'),
                    'organization_id' => $org->id,
                ]);
            }
            Auth::login($user);
        }

        /** @var User $user */
        $user = Auth::user();

        // Ensure user is associated with an organization
        if ($user->organization_id === null) {
            $org = Organization::first() ?: Organization::create([
                'name' => 'Demo Org',
                'slug' => 'demo-org',
                'is_active' => true,
            ]);
            $user->update(['organization_id' => $org->id]);
        }

        return $user;
    }

    /**
     * Show the dashboard with document list and metrics.
     */
    public function index(): View
    {
        $user = $this->ensureAuthenticatedUser();

        $documents = Document::query()
            ->where('organization_id', $user->organization_id)
            ->with('uploader:id,name')
            ->orderByDesc('created_at')
            ->get();

        $analyzedCount = $documents->where('status', DocumentStatus::Processed)->count();

        // Calculate risk clauses count across all documents
        $riskCount = 0;
        $totalTimeMs = 0;
        $timeCount = 0;

        foreach ($documents as $doc) {
            $latestAnalysis = DocumentAnalysis::query()
                ->where('document_id', $doc->id)
                ->where('status', DocumentStatus::Processed)
                ->orderByDesc('created_at')
                ->first();

            if ($latestAnalysis) {
                // If there's a risk assessment or summary, check for risks list
                $content = $latestAnalysis->content;
                if (isset($content['riesgos_identificados']) && is_array($content['riesgos_identificados'])) {
                    $riskCount += count($content['riesgos_identificados']);
                } elseif (isset($content['riesgos']) && is_array($content['riesgos'])) {
                    $riskCount += count($content['riesgos']);
                }

                if ($latestAnalysis->processing_time_ms > 0) {
                    $totalTimeMs += $latestAnalysis->processing_time_ms;
                    $timeCount++;
                }
            }
        }

        $avgTime = $timeCount > 0 ? number_format(($totalTimeMs / $timeCount) / 1000, 1) : '--';

        $planSlug = config('billing.default_plan', 'free');
        $activePlan = $user->organization?->activePlan();
        if ($activePlan !== null) {
            $planSlug = $activePlan->slug;
        }

        $planConfig = (array) config("billing.plans.{$planSlug}", config('billing.plans.free', []));

        $periodStart = now()->startOfMonth();

        $documentsThisMonth = Document::query()
            ->where('organization_id', $user->organization_id)
            ->where('created_at', '>=', $periodStart)
            ->count();

        $analysesThisMonth = DocumentAnalysis::query()
            ->where('organization_id', $user->organization_id)
            ->where('created_at', '>=', $periodStart)
            ->count();

        $storageUsedBytes = (int) Document::query()
            ->where('organization_id', $user->organization_id)
            ->sum('file_size_bytes');

        return view('dashboard', compact(
            'documents',
            'analyzedCount',
            'riskCount',
            'avgTime',
            'planSlug',
            'planConfig',
            'documentsThisMonth',
            'analysesThisMonth',
            'storageUsedBytes',
        ));
    }

    /**
     * Show detailed analysis of a document.
     */
    public function show(Document $document): View
    {
        $user = $this->ensureAuthenticatedUser();

        // Enforce tenant isolation
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
        $user = $this->ensureAuthenticatedUser();

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
        $user = $this->ensureAuthenticatedUser();

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:51200', // 50MB
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

            // Execute the action (stores, saves to DB, dispatches job)
            $document = $this->storeAction->execute(
                dto: $dto,
                ipAddress: $request->ip(),
            );

            // Since it runs in the background, we can inform the user.
            // But if the queue is running synchronously (e.g. testing or sync driver),
            // it will finish immediately.
            return redirect()
                ->route('dashboard')
                ->with('success', 'Documento subido correctamente. El procesamiento ha comenzado en segundo plano.');
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
