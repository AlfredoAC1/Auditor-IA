<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_sees_standard_dashboard(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $org = Organization::create(['name' => 'Free Org', 'slug' => 'free-org', 'is_active' => true]);
        $user = User::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard');
        $response->assertSee('Dashboard');
        $response->assertDontSee('Dashboard Pro');
    }

    public function test_pro_user_sees_pro_dashboard(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();
        $org = Organization::create(['name' => 'Pro Org', 'slug' => 'pro-org', 'is_active' => true]);

        Subscription::create([
            'organization_id' => $org->id,
            'plan_id' => $proPlan->id,
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        $user = User::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard-pro');
        $response->assertSee('Dashboard Pro');
        $response->assertSee('Tipos de análisis disponibles');
    }

    public function test_demo_pro_user_can_login(): void
    {
        $this->seed(\Database\Seeders\DemoUsersSeeder::class);

        $response = $this->post(route('login.store'), [
            'email' => 'pro@auditor-ia.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
