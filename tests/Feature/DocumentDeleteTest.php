<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Documents\DeleteDocumentAction;
use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;

use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $this->organization = Organization::create([
            'name' => 'Org Test',
            'slug' => 'org-test',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_user_can_delete_document_and_associated_analysis_in_cascade(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('documents/1/2026/08/test.pdf', 'dummy content');

        $document = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'test.pdf',
            'stored_filename' => 'test.pdf',
            'storage_path' => 'documents/1/2026/08',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'dummy content'),
            'status' => DocumentStatus::Processed,
        ]);

        $analysis = DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $this->organization->id,
            'requested_by' => $this->user->id,
            'analysis_type' => AnalysisType::FullSummary,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['resumen' => 'Prueba'],
            'status' => DocumentStatus::Processed,
            'version' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('dashboard.documents.destroy', $document->id));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        $this->assertDatabaseMissing('document_analyses', ['id' => $analysis->id]);
        Storage::disk('local')->assertMissing('documents/1/2026/08/test.pdf');
    }

    public function test_cannot_delete_another_organizations_document(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Other Org',
            'slug' => 'other-org',
            'is_active' => true,
        ]);

        $otherDoc = Document::create([
            'organization_id' => $otherOrg->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $otherOrg->id])->id,
            'original_filename' => 'secret.pdf',
            'stored_filename' => 'secret.pdf',
            'storage_path' => 'documents/2/2026/08',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 512,
            'file_hash' => 'hash',
            'status' => DocumentStatus::Processed,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('dashboard.documents.destroy', $otherDoc->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('documents', ['id' => $otherDoc->id]);
    }

    public function test_prune_free_documents_command_deletes_free_documents_older_than_7_days(): void
    {
        Storage::fake('local');

        // Create expired document for Free Org (8 days old)
        $expiredFreeDoc = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'expired_free.pdf',
            'stored_filename' => 'expired_free.pdf',
            'storage_path' => 'documents/1/2026/08',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => 'hash1',
            'status' => DocumentStatus::Processed,
        ]);
        $expiredFreeDoc->forceFill(['created_at' => now()->subDays(8)])->save();

        // Create Pro Org with active Pro subscription
        $proPlan = Plan::where('slug', 'pro')->firstOrFail();
        $proOrg = Organization::create(['name' => 'Pro Org', 'slug' => 'pro-org', 'is_active' => true]);
        Subscription::create([
            'organization_id' => $proOrg->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        // Create expired document for Pro Org (8 days old)
        $expiredProDoc = Document::create([
            'organization_id' => $proOrg->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $proOrg->id])->id,
            'original_filename' => 'expired_pro.pdf',
            'stored_filename' => 'expired_pro.pdf',
            'storage_path' => 'documents/2/2026/08',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'file_hash' => 'hash2',
            'status' => DocumentStatus::Processed,
        ]);
        $expiredProDoc->forceFill(['created_at' => now()->subDays(8)])->save();

        // Run artisan command
        $this->artisan('documents:prune-free --days=7')
            ->assertExitCode(0);

        // Free expired document should be purged
        $this->assertDatabaseMissing('documents', ['id' => $expiredFreeDoc->id]);

        // Pro expired document should be retained
        $this->assertDatabaseHas('documents', ['id' => $expiredProDoc->id]);
    }
}
