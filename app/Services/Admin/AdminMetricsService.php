<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\AnalysisType;
use App\Models\BugTicket;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AdminMetricsService
{
    /**
     * Get system-wide high-level KPI cards data.
     *
     * @return array{
     *     total_users: int,
     *     total_organizations: int,
     *     total_analyses: int,
     *     total_tokens: int,
     *     new_tickets_count: int,
     *     resolved_tickets_count: int,
     * }
     */
    public function getSummaryStats(): array
    {
        $totalUsers = User::query()->count();
        $totalOrgs = Organization::query()->count();
        $totalAnalyses = DocumentAnalysis::query()->count();

        $totalTokens = (int) DocumentAnalysis::query()
            ->sum(DB::raw('COALESCE(input_tokens, 0) + COALESCE(output_tokens, 0)'));

        $newTicketsCount = BugTicket::query()->new()->count();
        $resolvedTicketsCount = BugTicket::query()->resolved()->count();

        return [
            'total_users' => $totalUsers,
            'total_organizations' => $totalOrgs,
            'total_analyses' => $totalAnalyses,
            'total_tokens' => $totalTokens,
            'new_tickets_count' => $newTicketsCount,
            'resolved_tickets_count' => $resolvedTicketsCount,
        ];
    }

    /**
     * Get the distribution of analyses per user (ranking).
     *
     * @return Collection<int, array{name: string, email: string, count: int}>
     */
    public function getAnalysesPerUser(int $limit = 8): Collection
    {
        return User::query()
            ->has('analyses')
            ->withCount('analyses')
            ->orderByDesc('analyses_count')
            ->limit($limit)
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'name' => $user->name,
                'email' => $user->email,
                'count' => (int) $user->getAttribute('analyses_count'),
            ]);
    }

    /**
     * Get daily token usage trend for the last N days.
     *
     * @return array{
     *     labels: list<string>,
     *     input_tokens: list<int>,
     *     output_tokens: list<int>,
     *     total_tokens: list<int>,
     * }
     */
    public function getTokenUsageTrend(int $days = 14): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $driver = DB::connection()->getDriverName();

        $dateSql = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', created_at)"
            : "to_char(created_at, 'YYYY-MM-DD')";

        $rows = DocumentAnalysis::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw("{$dateSql} as day, SUM(COALESCE(input_tokens, 0)) as in_tokens, SUM(COALESCE(output_tokens, 0)) as out_tokens")
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $inputTokens = [];
        $outputTokens = [];
        $totalTokens = [];

        for ($i = 0; $i < $days; $i++) {
            $currentDay = $startDate->copy()->addDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($currentDay)->format('d M');

            $row = $rows->get($currentDay);
            $in = $row ? (int) $row->in_tokens : 0;
            $out = $row ? (int) $row->out_tokens : 0;

            $inputTokens[] = $in;
            $outputTokens[] = $out;
            $totalTokens[] = $in + $out;
        }

        return [
            'labels' => $labels,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
        ];
    }

    /**
     * Get top token-consuming users.
     *
     * @return Collection<int, array{name: string, email: string, input_tokens: int, output_tokens: int, total_tokens: int}>
     */
    public function getTokenUsageByUser(int $limit = 8): Collection
    {
        return User::query()
            ->has('analyses')
            ->withSum('analyses as in_tokens', 'input_tokens')
            ->withSum('analyses as out_tokens', 'output_tokens')
            ->orderByDesc(DB::raw('COALESCE(in_tokens, 0) + COALESCE(out_tokens, 0)'))
            ->limit($limit)
            ->get(['id', 'name', 'email'])
            ->map(function (User $user): array {
                $in = (int) $user->getAttribute('in_tokens');
                $out = (int) $user->getAttribute('out_tokens');

                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'input_tokens' => $in,
                    'output_tokens' => $out,
                    'total_tokens' => $in + $out,
                ];
            });
    }

    /**
     * Get breakdown of analyses by analysis type.
     *
     * @return array<string, int>
     */
    public function getAnalysisTypeDistribution(): array
    {
        $distribution = DocumentAnalysis::query()
            ->selectRaw('analysis_type, count(*) as aggregate')
            ->groupBy('analysis_type')
            ->pluck('aggregate', 'analysis_type')
            ->all();

        $result = [];
        foreach (AnalysisType::cases() as $type) {
            $result[$type->label()] = (int) ($distribution[$type->value] ?? 0);
        }

        return $result;
    }
}
