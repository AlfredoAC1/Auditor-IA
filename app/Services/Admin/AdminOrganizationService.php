<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class AdminOrganizationService
{
    /**
     * Get paginated organizations with member/document/analysis statistics.
     *
     * @return LengthAwarePaginator<Organization>
     */
    public function getPaginatedOrganizations(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Organization::query()
            ->withCount(['users', 'documents', 'analyses'])
            ->with(['activeSubscription.plan', 'creditLedger'])
            ->when($search, function (Builder $query, string $term): void {
                $trimmed = trim($term);
                $query->where(function (Builder $q) use ($trimmed): void {
                    $q->where('name', 'like', "%{$trimmed}%")
                        ->orWhere('slug', 'like', "%{$trimmed}%")
                        ->orWhere('tax_id', 'like', "%{$trimmed}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
