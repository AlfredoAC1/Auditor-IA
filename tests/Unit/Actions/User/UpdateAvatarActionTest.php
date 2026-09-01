<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\UpdateAvatarAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateAvatarActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAvatarAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new UpdateAvatarAction;
    }

    public function test_stores_avatar_and_updates_user_when_no_previous_avatar_exists(): void
    {
        Storage::fake('local');
        Log::spy();

        $user = User::factory()->create([
            'avatar_path' => null,
        ]);

        $file = UploadedFile::fake()->create('avatar.png', 100, 'image/png');

        $path = $this->action->execute($user, $file);

        $expectedPath = "avatars/{$user->id}/avatar.png";
        $this->assertEquals($expectedPath, $path);
        $this->assertEquals($expectedPath, $user->fresh()->avatar_path);
        Storage::disk('local')->assertExists($expectedPath);

        Log::shouldHaveReceived('info')
            ->with('User avatar updated.', [
                'user_id' => $user->id,
                'path' => $expectedPath,
            ]);
    }

    public function test_deletes_old_avatar_when_updating_avatar(): void
    {
        Storage::fake('local');
        Log::spy();

        $oldAvatarPath = 'avatars/old_avatar.png';
        Storage::disk('local')->put($oldAvatarPath, 'old content');
        Storage::disk('local')->assertExists($oldAvatarPath);

        $user = User::factory()->create([
            'avatar_path' => $oldAvatarPath,
        ]);

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $path = $this->action->execute($user, $file);

        $expectedPath = "avatars/{$user->id}/avatar.jpg";
        $this->assertEquals($expectedPath, $path);
        $this->assertEquals($expectedPath, $user->fresh()->avatar_path);
        Storage::disk('local')->assertMissing($oldAvatarPath);
        Storage::disk('local')->assertExists($expectedPath);
    }

    public function test_throws_exception_when_file_storage_fails(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'avatar_path' => null,
        ]);

        $file = \Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getClientOriginalExtension')->once()->andReturn('png');
        $file->shouldReceive('storeAs')
            ->once()
            ->with("avatars/{$user->id}", 'avatar.png', 'local')
            ->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to store avatar file.');

        $this->action->execute($user, $file);
    }
}
