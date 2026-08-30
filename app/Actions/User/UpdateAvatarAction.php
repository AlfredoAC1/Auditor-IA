<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class UpdateAvatarAction
{
    private const DISK = 'local';

    /**
     * Store the new avatar, delete the old one, and persist the path.
     */
    public function execute(User $user, UploadedFile $file): string
    {
        // Remove the previous avatar if it exists
        if ($user->avatar_path !== null) {
            Storage::disk(self::DISK)->delete($user->avatar_path);
        }

        $extension = $file->getClientOriginalExtension();
        $path      = $file->storeAs(
            "avatars/{$user->id}",
            "avatar.{$extension}",
            self::DISK,
        );

        if ($path === false) {
            throw new \RuntimeException('Failed to store avatar file.');
        }

        $user->update(['avatar_path' => $path]);

        Log::info('User avatar updated.', [
            'user_id' => $user->id,
            'path'    => $path,
        ]);

        return $path;
    }
}
