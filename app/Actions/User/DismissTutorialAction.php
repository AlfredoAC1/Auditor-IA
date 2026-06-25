<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;

final class DismissTutorialAction
{
    /**
     * Mark the tutorial as dismissed for the given user.
     */
    public function execute(User $user): void
    {
        $user->update([
            'dismissed_tutorial' => true,
        ]);
    }
}
