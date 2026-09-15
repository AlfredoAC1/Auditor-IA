<?php

declare(strict_types=1);

namespace Tests\Unit\Services\LLM;

use App\Models\Document;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\LLM\LlmAnalysisService;
use App\Services\LLM\LlmPromptBuilder;
use App\Services\LLM\LlmServiceResolver;
use App\Services\LLM\ProLLMAnalysisService;
use App\Services\LLM\ProLlmPromptBuilder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for LlmServiceResolver.
 *
 * Ensures strict separation between Free (Cerebras) and Pro (Qwen Cloud) pipelines.
 *
 * NOTE: Pure database & logic test — NO network calls.
 */
class LlmServiceResolverTest extends TestCase
{
    use RefreshDatabase;

    private LlmServiceResolver $resolver;

    private Organization $freeOrg;

    private Organization $proOrg;

    private Organization $lifetimeOrg;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $freeService = new LlmAnalysisService(new LlmPromptBuilder);
        $proService = new ProLLMAnalysisService(new ProLlmPromptBuilder);

        $this->resolver = new LlmServiceResolver($freeService, $proService);

        // Free organization (no subscription)
        $this->freeOrg = Organization::create([
            'name' => 'Organización Free',
            'slug' => 'org-free',
            'is_active' => true,
        ]);

        // Pro organization
        $this->proOrg = Organization::create([
            'name' => 'Organización Pro',
            'slug' => 'org-pro',
            'is_active' => true,
        ]);
        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();
        Subscription::create([
            'organization_id' => $this->proOrg->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
        ]);

        // Lifetime organization
        $this->lifetimeOrg = Organization::create([
            'name' => 'Organización Lifetime',
            'slug' => 'org-lifetime',
            'is_active' => true,
        ]);
        $lifetimePlan = Plan::query()->where('slug', 'lifetime')->firstOrFail();
        Subscription::create([
            'organization_id' => $this->lifetimeOrg->id,
            'plan_id' => $lifetimePlan->id,
            'status' => 'active',
        ]);
    }

    public function test_resolves_free_service_for_free_organization(): void
    {
        $service = $this->resolver->resolveForOrganization($this->freeOrg);

        $this->assertInstanceOf(LlmAnalysisService::class, $service);
    }

    public function test_resolves_pro_service_for_pro_organization(): void
    {
        $service = $this->resolver->resolveForOrganization($this->proOrg);

        $this->assertInstanceOf(ProLLMAnalysisService::class, $service);
    }

    public function test_resolves_pro_service_for_lifetime_organization(): void
    {
        $service = $this->resolver->resolveForOrganization($this->lifetimeOrg);

        $this->assertInstanceOf(ProLLMAnalysisService::class, $service);
    }

    public function test_resolves_service_based_on_document_ownership(): void
    {
        $freeDoc = new Document([
            'organization_id' => $this->freeOrg->id,
            'original_filename' => 'free_doc.pdf',
        ]);
        $freeDoc->setRelation('organization', $this->freeOrg);

        $proDoc = new Document([
            'organization_id' => $this->proOrg->id,
            'original_filename' => 'pro_doc.pdf',
        ]);
        $proDoc->setRelation('organization', $this->proOrg);

        $this->assertInstanceOf(LlmAnalysisService::class, $this->resolver->resolveForDocument($freeDoc));
        $this->assertInstanceOf(ProLLMAnalysisService::class, $this->resolver->resolveForDocument($proDoc));
    }

    public function test_resolves_service_for_user(): void
    {
        $freeUser = User::factory()->create(['organization_id' => $this->freeOrg->id]);
        $freeUser->setRelation('organization', $this->freeOrg);

        $proUser = User::factory()->create(['organization_id' => $this->proOrg->id]);
        $proUser->setRelation('organization', $this->proOrg);

        $this->assertInstanceOf(LlmAnalysisService::class, $this->resolver->resolveForUser($freeUser));
        $this->assertInstanceOf(ProLLMAnalysisService::class, $this->resolver->resolveForUser($proUser));
    }
}
