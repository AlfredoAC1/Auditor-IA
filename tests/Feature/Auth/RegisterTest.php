<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Crea tu cuenta');
        $response->assertSee('Crear cuenta gratis');
    }

    public function test_new_user_can_register_successfully(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Constanza Silva',
            'email' => 'constanza@silvalegal.cl',
            'organization_name' => 'Silva & Asociados',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::where('email', 'constanza@silvalegal.cl')->first();
        $this->assertNotNull($user);
        $this->assertSame('Constanza Silva', $user->name);
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));

        // Check organization
        $organization = $user->organization;
        $this->assertNotNull($organization);
        $this->assertSame('Silva & Asociados', $organization->name);
        $this->assertTrue($organization->is_active);

        // Check credit ledger initialized
        $ledger = $organization->creditLedger;
        $this->assertNotNull($ledger);
        $this->assertGreaterThan(0, $ledger->credits_quota);
        $this->assertSame($ledger->credits_quota, $ledger->credits_remaining);
    }

    public function test_new_user_can_register_without_explicit_organization_name(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Roberto Lagos',
            'email' => 'roberto@lagos.cl',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'roberto@lagos.cl')->first();
        $this->assertNotNull($user);
        $this->assertSame('Espacio de Roberto Lagos', $user->organization->name);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        $org = Organization::create([
            'name' => 'Existing Org',
            'slug' => 'existing-org',
            'is_active' => true,
        ]);

        User::factory()->create([
            'email' => 'duplicate@example.com',
            'organization_id' => $org->id,
        ]);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_mismatched_password_confirmation(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Carlos Perez',
            'email' => 'carlos@perez.cl',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass456!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_short_password(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Carlos Perez',
            'email' => 'carlos@perez.cl',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_accepting_terms(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Carlos Perez',
            'email' => 'carlos@perez.cl',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            // 'terms' omitted
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['terms']);
        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_register_screen(): void
    {
        $org = Organization::create([
            'name' => 'Existing Org',
            'slug' => 'existing-org-2',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }
}
