<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\AiModel;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\AiCreditService;
use Database\Seeders\AiModelSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCreditServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiCreditService $service;
    private Organization $freeOrg;
    private Organization $proOrg;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->seed(AiModelSeeder::class);

        $this->service = new AiCreditService();

        $this->freeOrg = Organization::create([
            'name' => 'Free Org',
            'slug' => 'free-org',
            'is_active' => true,
        ]);

        $this->proOrg = Organization::create([
            'name' => 'Pro Org',
            'slug' => 'pro-org',
            'is_active' => true,
        ]);

        // Attach Pro subscription to proOrg
        $proPlan = Plan::where('slug', 'pro')->firstOrFail();
        $this->proOrg->subscriptions()->create([
            'plan_id' => $proPlan->id,
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'organization_id' => $this->freeOrg->id,
        ]);
    }

    public function test_provisions_free_tier_credits_with_weekly_expiry(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->freeOrg);

        $this->assertEquals(25, $ledger->credits_remaining);
        $this->assertEquals(25, $ledger->credits_quota);
        $this->assertNotNull($ledger->period_expires_at);
        $this->assertTrue(now()->addDays(7)->diffInHours($ledger->period_expires_at) < 2);
    }

    public function test_provisions_pro_tier_credits_with_monthly_expiry(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->proOrg);

        $this->assertEquals(500, $ledger->credits_remaining);
        $this->assertEquals(500, $ledger->credits_quota);
        $this->assertNotNull($ledger->period_expires_at);
        $this->assertTrue(now()->addMonth()->diffInHours($ledger->period_expires_at) < 2);
    }

    public function test_free_users_can_only_use_lightweight_tier_models(): void
    {
        $lightweight = AiModel::where('tier', 'lightweight')->firstOrFail();
        $standard = AiModel::where('tier', 'standard')->firstOrFail();

        $this->assertTrue($this->service->canPerformAnalysis($this->freeOrg, $lightweight));
        $this->assertFalse($this->service->canPerformAnalysis($this->freeOrg, $standard));
    }

    public function test_pro_users_can_use_standard_and_advanced_models(): void
    {
        $lightweight = AiModel::where('tier', 'lightweight')->firstOrFail();
        $standard = AiModel::where('tier', 'standard')->firstOrFail();

        $this->assertTrue($this->service->canPerformAnalysis($this->proOrg, $lightweight));
        $this->assertTrue($this->service->canPerformAnalysis($this->proOrg, $standard));
    }

    public function test_deducts_credits_according_to_model_multiplier(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->proOrg);
        $initialBalance = $ledger->credits_remaining;

        $standardModel = AiModel::where('tier', 'standard')->firstOrFail(); // 2x multiplier = 2 credits
        $cost = $standardModel->creditCostPerAnalysis();
        $this->assertEquals(2, $cost);

        $document = Document::create([
            'organization_id' => $this->proOrg->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'doc.pdf',
            'stored_filename' => 'uuid.pdf',
            'storage_path' => 'documents',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 500,
            'file_hash' => 'hash',
            'status' => 'processed',
        ]);

        $analysis = DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $this->proOrg->id,
            'requested_by' => $this->user->id,
            'analysis_type' => 'full_summary',
            'llm_provider' => 'groq',
            'llm_model' => $standardModel->slug,
            'content' => [],
            'status' => 'processed',
            'version' => 1,
        ]);

        $this->service->deductCredits($this->proOrg, $this->user, $standardModel, $analysis);

        $this->assertEquals($initialBalance - 2, $ledger->fresh()->credits_remaining);

        $this->assertDatabaseHas('credit_transactions', [
            'organization_id' => $this->proOrg->id,
            'analysis_id' => $analysis->id,
            'credits_amount' => -2,
        ]);
    }

    public function test_weekly_quota_refreshes_when_period_expires(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->freeOrg);

        // Simulate expired period and consumed credits
        $ledger->update([
            'credits_remaining' => 0,
            'period_expires_at' => now()->subHour(),
        ]);

        // Next call should trigger weekly refresh back to 25
        $refreshedLedger = $this->service->checkAndRefreshQuota($this->freeOrg);

        $this->assertEquals(25, $refreshedLedger->credits_remaining);
        $this->assertDatabaseHas('credit_transactions', [
            'organization_id' => $this->freeOrg->id,
            'type' => 'quota_refresh',
            'credits_amount' => 25,
        ]);
    }
}
