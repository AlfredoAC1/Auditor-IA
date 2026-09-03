<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminMetricsService;
use Illuminate\View\View;

final class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminMetricsService $metricsService,
    ) {}

    /**
     * Display the Admin overview dashboard with KPIs and interactive analytics charts.
     */
    public function index(): View
    {
        $stats = $this->metricsService->getSummaryStats();
        $analysesPerUser = $this->metricsService->getAnalysesPerUser();
        $tokenTrend = $this->metricsService->getTokenUsageTrend();
        $tokenUsageByUser = $this->metricsService->getTokenUsageByUser();
        $analysisTypes = $this->metricsService->getAnalysisTypeDistribution();

        return view('admin.dashboard', [
            'stats' => $stats,
            'analysesPerUser' => $analysesPerUser,
            'tokenTrend' => $tokenTrend,
            'tokenUsageByUser' => $tokenUsageByUser,
            'analysisTypes' => $analysisTypes,
        ]);
    }
}
