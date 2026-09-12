<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Enums\DocumentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Document;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
        $this->seed(PlanSeeder::class);

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

    public function test_authenticated_user_can_dismiss_ancillary_tutorial(): void
    {
        $org = Organization::create(['name' => 'Demo Org', 'slug' => 'demo-org', 'is_active' => true]);
        $user = User::factory()->create([
            'organization_id' => $org->id,
            'dismissed_ancillary_tutorial' => false,
        ]);

        $this->assertFalse($user->dismissed_ancillary_tutorial);

        $response = $this->actingAs($user)->postJson(route('user.dismiss-ancillary-tutorial'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Tutorial descartado correctamente.',
        ]);

        $this->assertTrue($user->fresh()->dismissed_ancillary_tutorial);
    }

    public function test_ancillary_tutorial_html_visibility_based_on_user_preference(): void
    {
        $this->seed(PlanSeeder::class);

        $org = Organization::create(['name' => 'Pro Org', 'slug' => 'pro-org', 'is_active' => true]);
        $org->subscriptions()->create([
            'plan_id' => Plan::where('slug', 'pro')->first()->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
        ]);

        // 1. User with tutorial enabled (dismissed_ancillary_tutorial = false)
        $userWithTutorial = User::factory()->create([
            'organization_id' => $org->id,
            'dismissed_ancillary_tutorial' => false,
        ]);

        $document = Document::create([
            'id' => Str::uuid(),
            'organization_id' => $org->id,
            'uploaded_by' => $userWithTutorial->id,
            'name' => 'Test Doc.pdf',
            'original_filename' => 'Test Doc.pdf',
            'stored_filename' => 'test-doc.pdf',
            'storage_path' => 'documents/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => 'hash123',
            'status' => DocumentStatus::Processed,
        ]);

        $response = $this->actingAs($userWithTutorial)->get(route('analysis.show', $document));
        $response->assertOk();
        $response->assertSee('id="ancillary-tutorial-container"', false);

        // 2. User with tutorial dismissed (dismissed_ancillary_tutorial = true)
        $userWithoutTutorial = User::factory()->create([
            'organization_id' => $org->id,
            'dismissed_ancillary_tutorial' => true,
        ]);

        $response2 = $this->actingAs($userWithoutTutorial)->get(route('analysis.show', $document));
        $response2->assertOk();
        $response2->assertDontSee('id="ancillary-tutorial-container"', false);
    }
}
