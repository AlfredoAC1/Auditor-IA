<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\DeleteAccountAction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteAccountActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAccountAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new DeleteAccountAction;
    }

    public function test_deletes_user_organization_tokens_and_avatar(): void
    {
        Storage::fake('local');
        Log::spy();

        $org = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
            'avatar_path' => 'avatars/user1.png',
        ]);

        Storage::disk('local')->put('avatars/user1.png', 'avatar content');
        Storage::disk('local')->assertExists('avatars/user1.png');

        $user->createToken('test-token');
        $this->assertCount(1, $user->tokens);

        $this->action->execute($user);

        Storage::disk('local')->assertMissing('avatars/user1.png');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);

        Log::shouldHaveReceived('warning')
            ->with('Account deletion initiated.', [
                'user_id' => $user->id,
                'organization_id' => $org->id,
            ]);

        Log::shouldHaveReceived('warning')
            ->with('Account deletion completed.', [
                'user_id' => $user->id,
                'organization_id' => $org->id,
            ]);
    }

    public function test_deletes_user_without_organization(): void
    {
        $user = User::factory()->create([
            'organization_id' => null,
            'avatar_path' => null,
        ]);

        $this->action->execute($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_deletes_user_without_avatar(): void
    {
        Storage::fake('local');

        $org = Organization::create([
            'name' => 'Test Org No Avatar',
            'slug' => 'test-org-no-avatar',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
            'avatar_path' => null,
        ]);

        $this->action->execute($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    public function test_deletes_user_when_organization_is_soft_deleted(): void
    {
        $org = Organization::create([
            'name' => 'Test Org Soft Deleted',
            'slug' => 'test-org-soft-deleted',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $org->delete();
        $this->assertSoftDeleted('organizations', ['id' => $org->id]);

        $this->action->execute($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }
}
