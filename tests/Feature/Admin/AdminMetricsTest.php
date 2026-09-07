<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Enums\UserRole;
use App\Models\BugTicket;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use App\Services\Admin\AdminMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMetricsTest extends TestCase
{
    use RefreshDatabase;

    private AdminMetricsService $service;

    private Organization $org;

    private User $user1;

    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AdminMetricsService;

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Analytics Org',
            'slug' => 'analytics-org',
            'is_active' => true,
        ]);

        $this->user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => 'password',
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        $this->user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => 'password',
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);
    }

    public function test_get_summary_stats(): void
    {
        $doc = Document::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'uploaded_by' => $this->user1->id,
            'original_filename' => 'test.pdf',
            'stored_filename' => 'test.pdf',
            'storage_path' => 'docs/test.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'content'),
            'status' => DocumentStatus::Processed->value,
        ]);

        DocumentAnalysis::create([
            'id' => (string) Str::uuid(),
            'document_id' => $doc->id,
            'organization_id' => $this->org->id,
            'requested_by' => $this->user1->id,
            'analysis_type' => AnalysisType::RiskAssessment,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['resumen' => 'ok'],
            'input_tokens' => 500,
            'output_tokens' => 250,
            'status' => DocumentStatus::Processed,
        ]);

        BugTicket::create([
            'user_id' => $this->user1->id,
            'organization_id' => $this->org->id,
            'title' => 'Test Ticket',
            'description' => 'Description',
        ]);

        $stats = $this->service->getSummaryStats();

        $this->assertEquals(2, $stats['total_users']);
        $this->assertEquals(1, $stats['total_organizations']);
        $this->assertEquals(1, $stats['total_analyses']);
        $this->assertEquals(750, $stats['total_tokens']);
        $this->assertEquals(1, $stats['new_tickets_count']);
        $this->assertEquals(0, $stats['resolved_tickets_count']);
    }

    public function test_analyses_per_user_and_token_usage_by_user(): void
    {
        $doc = Document::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->org->id,
            'uploaded_by' => $this->user1->id,
            'original_filename' => 'contract.pdf',
            'stored_filename' => 'contract.pdf',
            'storage_path' => 'docs/contract.pdf',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'file_hash' => hash('sha256', 'contract'),
            'status' => DocumentStatus::Processed->value,
        ]);

        DocumentAnalysis::create([
            'id' => (string) Str::uuid(),
            'document_id' => $doc->id,
            'organization_id' => $this->org->id,
            'requested_by' => $this->user1->id,
            'analysis_type' => AnalysisType::ClauseAnalysis,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['clausulas' => []],
            'input_tokens' => 1200,
            'output_tokens' => 800,
            'status' => DocumentStatus::Processed,
        ]);

        $analysesPerUser = $this->service->getAnalysesPerUser();
        $this->assertNotEmpty($analysesPerUser);
        $this->assertEquals('User One', $analysesPerUser->first()['name']);
        $this->assertEquals(1, $analysesPerUser->first()['count']);

        $tokenUsageByUser = $this->service->getTokenUsageByUser();
        $this->assertNotEmpty($tokenUsageByUser);
        $this->assertEquals(2000, $tokenUsageByUser->first()['total_tokens']);
        $this->assertEquals(1200, $tokenUsageByUser->first()['input_tokens']);
        $this->assertEquals(800, $tokenUsageByUser->first()['output_tokens']);
    }
}
