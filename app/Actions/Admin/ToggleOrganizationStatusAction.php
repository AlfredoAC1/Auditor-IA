<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class ToggleOrganizationStatusAction
{
    /**
     * Toggle active state of an organization.
     */
    public function execute(Organization $organization, ?User $adminUser = null): Organization
    {
        $organization->is_active = ! $organization->is_active;
        $organization->save();

        Log::info('Admin toggled organization status.', [
            'admin_id' => $adminUser?->id,
            'organization_id' => $organization->id,
            'new_status' => $organization->is_active ? 'active' : 'inactive',
        ]);

        return $organization;
    }
}
