<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Models\AiCreditLedger;      
use App\Models\AiModel;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DashboardMetricsService
{
    /**
     * @return array{
     *     documents: Collection<int, Document>,
     *     analyzedCount: int,
     *     riskCount: int,
     *     avgTime: string,
     *     planSlug: string,
     *     planConfig: array<string, mixed>,
     *     documentsThisMonth: int,
     *     analysesThisMonth: int,
     *     storageUsedBytes: int,
     *     tokensThisMonth: int,
     *     creditsRemaining: int,
     *     creditsQuota: int,
     *     creditRefreshInterval: string,
     *     periodExpiresAt: ?Carbon,
     *     allowsModelSelection: bool,
     *     analysisTypeCounts: array<string, int>,
     *     pendingDocumentIds: list<string>,
     *     availableModels: Collection<int, AiModel>,
     * }
     */
    public function forOrganization(User $user): array
    {
        $organizationId = (int) $user->organization_id;

        $documents = Document::query()
            ->where('organization_id', $organizationId)
            ->with(['uploader:id,name', 'latestAnalysis'])
            ->orderByDesc('created_at')
            ->get();

        $analyzedCount = $documents->where('status', DocumentStatus::Processed)->count();

        $riskCount = 0;
        $totalTimeMs = 0;
        $timeCount = 0;

        foreach ($documents as $doc) {
            $latestAnalysis = $doc->latestAnalysis;
            if ($latestAnalysis !== null) {
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

        $organization = $user->organization ?? Organization::find($organizationId);
        $planSlug = $organization?->currentPlanSlug() ?? config('billing.default_plan', 'free');
        $planConfig = (array) config("billing.plans.{$planSlug}", config('billing.plans.free', []));

        $periodStart = now()->startOfMonth();

        $documentsThisMonth = $documents
            ->filter(fn (Document $doc): bool => $doc->created_at !== null && $doc->created_at->gte($periodStart))
            ->count();

        $analysesThisMonth = DocumentAnalysis::query()
            ->where('organization_id', $organizationId)
            ->where('created_at', '>=', $periodStart)
            ->count();

        $storageUsedBytes = (int) $documents->sum('file_size_bytes');

        $tokensThisMonth = (int) DocumentAnalysis::query()
            ->where('organization_id', $organizationId)
            ->where('created_at', '>=', $periodStart)
            ->sum(DB::raw('COALESCE(input_tokens, 0) + COALESCE(output_tokens, 0)'));

        $typeCountsFromDb = DocumentAnalysis::query()
            ->where('organization_id', $organizationId)
            ->where('status', DocumentStatus::Processed)
            ->where('created_at', '>=', $periodStart)
            ->selectRaw('analysis_type, count(*) as aggregate')
            ->groupBy('analysis_type')
            ->pluck('aggregate', 'analysis_type')
            ->all();

        $analysisTypeCounts = [];
        foreach (AnalysisType::cases() as $type) {
            $analysisTypeCounts[$type->value] = (int) ($typeCountsFromDb[$type->value] ?? 0);
        }

        $pendingDocumentIds = $documents
            ->filter(fn (Document $doc): bool => in_array($doc->status, [DocumentStatus::Pending, DocumentStatus::Processing], true))
            ->pluck('id')
            ->values()
            ->all();

        // AI Credit ledger data
        $creditLedger = $organization?->getOrCreateCreditLedger();
        $plan = $organization?->currentPlan();

        $creditsRemaining = $creditLedger?->credits_remaining ?? 0;
        $creditsQuota = $creditLedger?->credits_quota ?? 0;
        $creditRefreshInterval = $plan?->credit_refresh_interval?->value ?? 'monthly';
        $periodExpiresAt = $creditLedger?->period_expires_at;
        $allowsModelSelection = $plan?->allowsModelSelection() ?? false;

        $availableModels = AiModel::query()
            ->active()
            ->ordered()
            ->get();

        return compact(
            'documents',
            'analyzedCount',
            'riskCount',
            'avgTime',
            'planSlug',
            'planConfig',
            'documentsThisMonth',
            'analysesThisMonth',
            'storageUsedBytes',
            'tokensThisMonth',
            'creditsRemaining',
            'creditsQuota',
            'creditRefreshInterval',
            'periodExpiresAt',
            'allowsModelSelection',
            'analysisTypeCounts',
            'pendingDocumentIds',
            'availableModels',
        );
    }
}
