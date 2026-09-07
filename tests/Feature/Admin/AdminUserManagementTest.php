<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'HQ Org',
            'slug' => 'hq-org',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Primary Admin',
            'email' => 'admin@hq.com',
            'password' => 'password123',
            'role' => UserRole::Admin,
            'organization_id' => $this->org->id,
        ]);
    }

    public function test_admin_can_create_new_administrator(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Second Admin',
            'email' => 'second.admin@hq.com',
            'password' => 'supersecret123',
            'role' => UserRole::Admin->value,
            'organization_id' => $this->org->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'second.admin@hq.com',
            'name' => 'Second Admin',
            'role' => UserRole::Admin->value,
        ]);

        $newUser = User::where('email', 'second.admin@hq.com')->firstOrFail();
        $this->assertTrue(Hash::check('supersecret123', $newUser->password));
        $this->assertTrue($newUser->isAdmin());
    }

    public function test_admin_can_create_regular_user_and_new_organization(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Client User',
            'email' => 'client@abogados.cl',
            'password' => 'clientpass123',
            'role' => UserRole::User->value,
            'new_organization_name' => 'Estudio Jurídico Silva',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'client@abogados.cl',
            'name' => 'Client User',
            'role' => UserRole::User->value,
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Estudio Jurídico Silva',
        ]);
    }

    public function test_admin_can_update_user_details_and_role(): void
    {
        $targetUser = User::create([
            'name' => 'Regular Member',
            'email' => 'member@hq.com',
            'password' => 'secret123',
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Promoted Member',
            'email' => 'promoted@hq.com',
            'role' => UserRole::Admin->value,
            'organization_id' => $this->org->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();
        $this->assertEquals('Promoted Member', $targetUser->name);
        $this->assertEquals('promoted@hq.com', $targetUser->email);
        $this->assertTrue($targetUser->isAdmin());
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin->id));

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_demote_the_last_administrator(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin->id), [
            'name' => 'Primary Admin',
            'email' => 'admin@hq.com',
            'role' => UserRole::User->value,
            'organization_id' => $this->org->id,
        ]);

        $response->assertSessionHasErrors('role');
        $this->admin->refresh();
        $this->assertTrue($this->admin->isAdmin());
    }

    public function test_admin_can_delete_another_user(): void
    {
        $targetUser = User::create([
            'name' => 'To Delete',
            'email' => 'delete.me@hq.com',
            'password' => 'secret123',
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $targetUser->id));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_public_registration_cannot_inject_admin_role(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Malicious User',
            'email' => 'hacker@test.com',
            'password' => 'HackerPass123!',
            'password_confirmation' => 'HackerPass123!',
            'terms' => '1',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'hacker@test.com')->firstOrFail();
        $this->assertSame(UserRole::User, $user->role);
        $this->assertFalse($user->isAdmin());
    }
}
