<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TutorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_dismiss_tutorial(): void
    {
        $response = $this->postJson(route('user.dismiss-tutorial'));
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_dismiss_tutorial(): void
    {
        $org = Organization::create(['name' => 'Demo Org', 'slug' => 'demo-org', 'is_active' => true]);
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'dismissed_tutorial' => false,
        ]);

        $this->assertFalse($user->dismissed_tutorial);

        $response = $this->actingAs($user)->postJson(route('user.dismiss-tutorial'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Tutorial descartado correctamente.',
        ]);

        $this->assertTrue($user->fresh()->dismissed_tutorial);
    }

    public function test_tutorial_html_visibility_based_on_user_preference(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $org = Organization::create(['name' => 'Demo Org', 'slug' => 'demo-org', 'is_active' => true]);
        
        // 1. User with tutorial enabled (dismissed_tutorial = false)
        $userWithTutorial = User::factory()->create([
            'organization_id' => $org->id,
            'dismissed_tutorial' => false,
        ]);

        $response = $this->actingAs($userWithTutorial)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('id="tutorial-container"', false);

        // 2. User with tutorial dismissed (dismissed_tutorial = true)
        $userWithoutTutorial = User::factory()->create([
            'organization_id' => $org->id,
            'dismissed_tutorial' => true,
        ]);

        $response2 = $this->actingAs($userWithoutTutorial)->get(route('dashboard'));
        $response2->assertOk();
        $response2->assertDontSee('id="tutorial-container"', false);
    }
}
