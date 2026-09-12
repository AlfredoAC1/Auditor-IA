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

        $this->assertEquals(100, $ledger->credits_remaining);
        $this->assertEquals(100, $ledger->credits_quota);
        $this->assertNotNull($ledger->period_expires_at);
        $this->assertTrue(now()->addDays(7)->diffInHours($ledger->period_expires_at) < 2);
    }

    public function test_provisions_pro_tier_credits_with_monthly_expiry(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->proOrg);

        $this->assertEquals(1000, $ledger->credits_remaining);
        $this->assertEquals(1000, $ledger->credits_quota);
        $this->assertNotNull($ledger->period_expires_at);
        $this->assertTrue(now()->addMonth()->diffInHours($ledger->period_expires_at) < 2);
    }

    public function test_provisions_lifetime_tier_credits_as_one_time_pro(): void
    {
        $lifetimeOrg = Organization::create([
            'name' => 'Lifetime Org',
            'slug' => 'lifetime-org',
            'is_active' => true,
        ]);
        $lifetimePlan = Plan::where('slug', 'lifetime')->firstOrFail();
        $lifetimeOrg->subscriptions()->create([
            'plan_id' => $lifetimePlan->id,
            'status' => 'active',
        ]);

        $ledger = $this->service->checkAndRefreshQuota($lifetimeOrg);

        $this->assertEquals(1000, $ledger->credits_remaining);
        $this->assertEquals(1000, $ledger->credits_quota);
        $this->assertFalse($ledger->isUnlimited());
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

    public function test_calculates_credits_based_on_tokens_and_model_multiplier(): void
    {
        // 7,000 tokens @ 1.0x weight = (7000 / 175) * 1.0 = 40 credits
        $cost1 = $this->service->calculateCreditsCost(5500, 1500, 1.0);
        $this->assertEquals(40, $cost1);

        // 7,000 tokens @ 2.0x weight = (7000 / 175) * 2.0 = 80 credits
        $cost2 = $this->service->calculateCreditsCost(5500, 1500, 2.0);
        $this->assertEquals(80, $cost2);

        // 9,500 tokens @ 3.0x weight = (9500 / 175) * 3.0 = 162.857 -> 163 credits
        $cost3 = $this->service->calculateCreditsCost(7500, 2000, 3.0);
        $this->assertEquals(163, $cost3);

        // Micro prompt: 70 tokens @ 1.0x weight = (70 / 175) * 1.0 = 0.4 -> min charge 1 credit
        $costMin = $this->service->calculateCreditsCost(40, 30, 1.0);
        $this->assertEquals(1, $costMin);
    }

    public function test_deducts_dynamic_token_based_credits(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->proOrg);
        $initialBalance = $ledger->credits_remaining;

        $standardModel = AiModel::where('tier', 'standard')->firstOrFail(); // 2.0x weight

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

        // 7,000 total tokens with standard (2.0x) = (7000 / 175) * 2 = 80 credits
        $analysis = DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $this->proOrg->id,
            'requested_by' => $this->user->id,
            'analysis_type' => 'full_summary',
            'llm_provider' => 'groq',
            'llm_model' => $standardModel->slug,
            'input_tokens' => 5500,
            'output_tokens' => 1500,
            'content' => [],
            'status' => 'processed',
            'version' => 1,
        ]);

        $this->service->deductCredits($this->proOrg, $this->user, $standardModel, $analysis);

        $this->assertEquals($initialBalance - 80, $ledger->fresh()->credits_remaining);

        $this->assertDatabaseHas('credit_transactions', [
            'organization_id' => $this->proOrg->id,
            'analysis_id' => $analysis->id,
            'credits_amount' => -80,
            'credits_balance_after' => $initialBalance - 80,
        ]);
    }

    public function test_allows_analysis_when_credits_positive_and_absorbs_overdraft(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->freeOrg);

        // Set remaining to 20 credits
        $ledger->update(['credits_remaining' => 20]);

        $lightweight = AiModel::where('tier', 'lightweight')->firstOrFail();

        // 20 remaining > 0, so analysis MUST be allowed!
        $this->assertTrue($this->service->canPerformAnalysis($this->freeOrg, $lightweight));

        $document = Document::create([
            'organization_id' => $this->freeOrg->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'doc.pdf',
            'stored_filename' => 'uuid.pdf',
            'storage_path' => 'documents',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 500,
            'file_hash' => 'hash2',
            'status' => 'processed',
        ]);

        // 7,000 total tokens with lightweight (1.0x) = (7000 / 175) * 1 = 40 credits cost
        $analysis = DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $this->freeOrg->id,
            'requested_by' => $this->user->id,
            'analysis_type' => 'full_summary',
            'llm_provider' => 'groq',
            'llm_model' => $lightweight->slug,
            'input_tokens' => 5500,
            'output_tokens' => 1500,
            'content' => [],
            'status' => 'processed',
            'version' => 1,
        ]);

        $this->service->deductCredits($this->freeOrg, $this->user, $lightweight, $analysis);

        // Balance drops to 0 (floored)
        $this->assertEquals(0, $ledger->fresh()->credits_remaining);

        // Credit transaction records the actual 20 deducted, with overdraft_absorbed = 20 in metadata
        $transaction = \App\Models\CreditTransaction::where('analysis_id', $analysis->id)->firstOrFail();
        $this->assertEquals(-20, $transaction->credits_amount);
        $this->assertEquals(0, $transaction->credits_balance_after);
        $this->assertEquals(40, $transaction->metadata['calculated_cost']);
        $this->assertEquals(20, $transaction->metadata['actual_deducted']);
        $this->assertEquals(20, $transaction->metadata['overdraft_absorbed']);
        $this->assertTrue($transaction->metadata['is_overdraft']);

        // Now with 0 remaining, next analysis must be BLOCKED!
        $this->assertFalse($this->service->canPerformAnalysis($this->freeOrg, $lightweight));
    }

    public function test_weekly_quota_refreshes_when_period_expires(): void
    {
        $ledger = $this->service->checkAndRefreshQuota($this->freeOrg);

        // Simulate expired period and consumed credits
        $ledger->update([
            'credits_remaining' => 0,
            'period_expires_at' => now()->subHour(),
        ]);

        // Next call should trigger weekly refresh back to 100
        $refreshedLedger = $this->service->checkAndRefreshQuota($this->freeOrg);

        $this->assertEquals(100, $refreshedLedger->credits_remaining);
        $this->assertDatabaseHas('credit_transactions', [
            'organization_id' => $this->freeOrg->id,
            'type' => 'quota_refresh',
            'credits_amount' => 100,
        ]);
    }
}
