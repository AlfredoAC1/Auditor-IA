<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardProviderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $organization = Organization::create([
            'name' => 'Organización de Prueba',
            'slug' => 'org-prueba',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
    }

    public function test_user_can_switch_llm_provider_on_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('dashboard.provider'), [
                'provider' => 'nvidia_nim',
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertEquals('nvidia_nim', session('llm_provider'));
    }

    public function test_dashboard_renders_cleanly_without_deprecated_provider_selector(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Proveedor de Inteligencia Artificial');
        $response->assertSee('Motor IA');
        $response->assertSee('Créditos IA');
    }
}
