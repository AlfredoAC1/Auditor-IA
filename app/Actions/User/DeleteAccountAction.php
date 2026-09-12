<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class DeleteAccountAction
{
    private const DISK = 'local';

    /**
     * Permanently delete the user, their organization, and all associated data.
     *
     * Cascade order:
     *  1. Revoke all Sanctum API tokens
     *  2. Delete avatar file from storage
     *  3. Force-delete the organization (cascades to documents, analyses, subscriptions, etc. via DB foreign keys)
     *  4. Delete the user record
     */
    public function execute(User $user): void
    {
        $userId = $user->id;
        $orgId = $user->organization_id;

        Log::warning('Account deletion initiated.', [
            'user_id' => $userId,
            'organization_id' => $orgId,
        ]);

        DB::transaction(function () use ($user, $orgId): void {
            // 1. Revoke all API tokens
            $user->tokens()->delete();

            // 2. Delete avatar from private storage
            if ($user->avatar_path !== null) {
                Storage::disk(self::DISK)->delete($user->avatar_path);
            }

            // 3. Wipe the organization (force-deletes even if soft-deleted)
            if ($orgId !== null) {
                $org = Organization::withTrashed()->find($orgId);
                $org?->forceDelete();
            }

            // 4. Delete the user record (org FK already gone)
            $user->forceDelete();
        });

        Log::warning('Account deletion completed.', [
            'user_id' => $userId,
            'organization_id' => $orgId,
        ]);
    }
}
