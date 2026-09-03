<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class AdminUserService
{
    /**
     * Get paginated users with search, filtering, and aggregated metrics.
     *
     * @param string|null $search
     * @param string|null $role
     * @param int|null $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<User>
     */
    public function getPaginatedUsers(
        ?string $search = null,
        ?string $role = null,
        ?int $organizationId = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return User::query()
            ->with(['organization:id,name,slug'])
            ->withCount('documents')
            ->withCount('analyses')
            ->withSum('analyses as total_tokens', DB::raw('COALESCE(input_tokens, 0) + COALESCE(output_tokens, 0)'))
            ->when($search, function (Builder $query, string $term): void {
                $trimmed = trim($term);
                $query->where(function (Builder $q) use ($trimmed): void {
                    $q->where('name', 'like', "%{$trimmed}%")
                      ->orWhere('email', 'like', "%{$trimmed}%");
                });
            })
            ->when($role && UserRole::tryFrom($role), function (Builder $query) use ($role): void {
                $query->where('role', $role);
            })
            ->when($organizationId, function (Builder $query, int $orgId): void {
                $query->where('organization_id', $orgId);
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
