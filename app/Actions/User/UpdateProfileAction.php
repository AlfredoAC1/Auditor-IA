<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Log;

final class UpdateProfileAction
{
    /**
     * Update the user's display name.
     */
    public function execute(User $user, string $name): void
    {
        $old = $user->name;

        $user->update(['name' => $name]);

        Log::info('User profile updated.', [
            'user_id' => $user->id,
            'old_name' => $old,
            'new_name' => $name,
        ]);
    }
}
