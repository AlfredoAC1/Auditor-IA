<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Models\DocumentAnalysis;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class AdminAnalysisService
{
    /**
     * Get paginated analyses across all tenants with eager relationships and filters.
     *
     * @param string|null $search
     * @param string|null $type
     * @param string|null $status
     * @param int|null $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<DocumentAnalysis>
     */
    public function getPaginatedAnalyses(
        ?string $search = null,
        ?string $type = null,
        ?string $status = null,
        ?int $organizationId = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return DocumentAnalysis::query()
            ->with([
                'document:id,original_filename',
                'organization:id,name,slug',
                'requester:id,name,email',
            ])
            ->when($search, function (Builder $query, string $term): void {
                $trimmed = trim($term);
                $query->whereHas('document', function (Builder $dq) use ($trimmed): void {
                    $dq->where('original_filename', 'like', "%{$trimmed}%");
                })->orWhereHas('requester', function (Builder $uq) use ($trimmed): void {
                    $uq->where('name', 'like', "%{$trimmed}%")
                       ->orWhere('email', 'like', "%{$trimmed}%");
                });
            })
            ->when($type && AnalysisType::tryFrom($type), function (Builder $query) use ($type): void {
                $query->where('analysis_type', $type);
            })
            ->when($status && DocumentStatus::tryFrom($status), function (Builder $query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($organizationId, function (Builder $query, int $orgId): void {
                $query->where('organization_id', $orgId);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
