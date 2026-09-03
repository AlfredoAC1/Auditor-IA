<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Services\Admin\AdminAnalysisService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminAnalysisController extends Controller
{
    public function __construct(
        private readonly AdminAnalysisService $analysisService,
    ) {}

    /**
     * Display a listing of analyses across all organizations.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString() ?: null;
        $type = $request->string('type')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;
        $organizationId = $request->filled('organization_id') ? (int) $request->input('organization_id') : null;

        $analyses = $this->analysisService->getPaginatedAnalyses($search, $type, $status, $organizationId);
        $organizations = Organization::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.analyses.index', [
            'analyses' => $analyses,
            'organizations' => $organizations,
            'types' => AnalysisType::cases(),
            'statuses' => DocumentStatus::cases(),
            'search' => $search,
            'selectedType' => $type,
            'selectedStatus' => $status,
            'selectedOrg' => $organizationId,
        ]);
    }

    /**
     * Display the specified analysis for in-depth audit inspection.
     */
    public function show(DocumentAnalysis $analysis): View
    {
        $analysis->load([
            'document',
            'organization',
            'requester',
            'supportingDocuments.document',
        ]);

        return view('admin.analyses.show', [
            'analysis' => $analysis,
        ]);
    }
}
