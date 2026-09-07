<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => UserRole::Admin,
            'organization_id' => $org->id,
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => 'password',
            'role' => UserRole::User,
            'organization_id' => $org->id,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_admin_dashboard(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_regular_user_is_forbidden_from_admin_users_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_regular_user_is_forbidden_from_admin_organizations(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.organizations.index'));

        $response->assertForbidden();
    }

    public function test_regular_user_is_forbidden_from_admin_analyses(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.analyses.index'));

        $response->assertForbidden();
    }

    public function test_regular_user_is_forbidden_from_admin_tickets(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.tickets.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Panel de Administración');
    }

    public function test_admin_can_access_users_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users.index');
        $response->assertSee('Gestión de Usuarios');
    }
}
