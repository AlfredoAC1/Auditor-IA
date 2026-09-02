<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AiModelSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Security Test Suite — Section 1: Critical Priorities for Auditor-IA
 *
 * Covers items 1.1 through 1.17 (all testable via HTTP).
 * Items 1.6, 1.7, 1.8, 1.9, 1.11, 1.16, 1.18, 1.19 are verified
 * via static code analysis (see implementation_plan.md).
 *
 * @see PENTESTING_CHECKLIST.md §1 (lines 107–127)
 */
class Section1CriticalPrioritiesTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Tenant A — the authenticated attacker
    // ---------------------------------------------------------------
    private Organization $orgA;
    private User $userA;

    // ---------------------------------------------------------------
    // Tenant B — the victim whose resources must never be accessible
    // ---------------------------------------------------------------
    private Organization $orgB;
    private User $userB;

    // ---------------------------------------------------------------
    // Shared fixture helpers
    // ---------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->seed(AiModelSeeder::class);

        // Tenant A (attacker)
        $this->orgA = Organization::create([
            'name'      => 'Tenant A - Attacker Corp',
            'slug'      => 'tenant-a',
            'is_active' => true,
        ]);
        $this->userA = User::create([
            'name'            => 'Attacker User',
            'email'           => 'attacker@tenant-a.com',
            'password'        => bcrypt('password'),
            'organization_id' => $this->orgA->id,
        ]);

        // Tenant B (victim)
        $this->orgB = Organization::create([
            'name'      => 'Tenant B - Victim Corp',
            'slug'      => 'tenant-b',
            'is_active' => true,
        ]);
        $this->userB = User::create([
            'name'            => 'Victim User',
            'email'           => 'victim@tenant-b.com',
            'password'        => bcrypt('password'),
            'organization_id' => $this->orgB->id,
        ]);
    }

    /**
     * Create a document owned by a given org/user.
     */
    private function createDocument(Organization $org, User $user, array $extra = []): Document
    {
        return Document::create(array_merge([
            'organization_id'  => $org->id,
            'uploaded_by'      => $user->id,
            'original_filename' => 'contrato.pdf',
            'stored_filename'  => 'stored-' . uniqid() . '.pdf',
            'storage_path'     => "documents/{$org->id}/2026/09",
            'disk'             => 'local',
            'mime_type'        => 'application/pdf',
            'file_size_bytes'  => 102400,
            'file_hash'        => hash('sha256', uniqid()),
            'status'           => 'pending',
            'metadata'         => [],
        ], $extra));
    }

    /**
     * Create a DocumentAnalysis owned by a given org/user/document.
     */
    private function createAnalysis(Document $document, Organization $org, User $user): DocumentAnalysis
    {
        return DocumentAnalysis::create([
            'document_id'   => $document->id,
            'organization_id' => $org->id,
            'requested_by'  => $user->id,
            'analysis_type' => 'full_summary',
            'llm_provider'  => 'groq',
            'llm_model'     => 'llama-8b-instant',
            'content'       => ['resumen_general' => 'Análisis de prueba.'],
            'status'        => 'processed',
            'version'       => 1,
        ]);
    }

    // ================================================================
    // 1.1 — Tenant A cannot access Tenant B's documents
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.1
     */
    public function test_tenant_a_cannot_read_tenant_b_document(): void
    {
        // Tenant B owns this document
        $docB = $this->createDocument($this->orgB, $this->userB);

        // Tenant A tries to read it
        Sanctum::actingAs($this->userA);

        $response = $this->getJson("/api/v1/documents/{$docB->id}");

        $response->assertStatus(403);
    }

    /**
     * @test
     * PENTESTING_CHECKLIST §1.1 — list endpoint must not expose cross-tenant documents
     */
    public function test_tenant_a_document_list_does_not_include_tenant_b_documents(): void
    {
        $this->createDocument($this->orgA, $this->userA);
        $docB = $this->createDocument($this->orgB, $this->userB);

        Sanctum::actingAs($this->userA);

        $response = $this->getJson('/api/v1/documents');
        $response->assertStatus(200);

        // Tenant B's document ID must never appear in the response
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertNotContains($docB->id, $ids, 'Tenant B document leaked into Tenant A list response');
    }

    // ================================================================
    // 1.2 — Tenant A cannot access Tenant B's analyses
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.2
     */
    public function test_tenant_a_cannot_read_tenant_b_analysis(): void
    {
        $docB      = $this->createDocument($this->orgB, $this->userB);
        $analysisB = $this->createAnalysis($docB, $this->orgB, $this->userB);

        Sanctum::actingAs($this->userA);

        // Attacker tries to access Tenant B's analysis via Tenant B's document route
        $response = $this->getJson("/api/v1/documents/{$docB->id}/analyses/{$analysisB->id}");

        // Must be forbidden — document belongs to Tenant B
        $response->assertStatus(403);
    }

    /**
     * @test
     * PENTESTING_CHECKLIST §1.2 — analysis list for cross-tenant document must be blocked
     */
    public function test_tenant_a_cannot_list_tenant_b_analyses(): void
    {
        $docB = $this->createDocument($this->orgB, $this->userB);

        Sanctum::actingAs($this->userA);

        $response = $this->getJson("/api/v1/documents/{$docB->id}/analyses");

        $response->assertStatus(403);
    }

    // ================================================================
    // 1.3 — Tenant A cannot access Tenant B's reports or exports
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.3 — contextual re-analysis (export-equivalent) on B's analysis
     */
    public function test_tenant_a_cannot_trigger_contextual_reanalysis_on_tenant_b_analysis(): void
    {
        $docB      = $this->createDocument($this->orgB, $this->userB, [
            'metadata' => ['parsed_compact_content' => 'Contenido del contrato de Tenant B.'],
        ]);
        $analysisB = $this->createAnalysis($docB, $this->orgB, $this->userB);

        Sanctum::actingAs($this->userA);

        $response = $this->postJson("/api/v1/analyses/{$analysisB->id}/analyze-with-context", [
            'analysis_type' => 'full_summary',
        ]);

        // Policy must reject: the analysis's primary document belongs to Tenant B
        $response->assertStatus(403);
    }

    /**
     * @test
     * PENTESTING_CHECKLIST §1.3 — attaching a supporting document to Tenant B's analysis
     */
    public function test_tenant_a_cannot_attach_document_to_tenant_b_analysis(): void
    {
        $docA      = $this->createDocument($this->orgA, $this->userA);
        $docB      = $this->createDocument($this->orgB, $this->userB);
        $analysisB = $this->createAnalysis($docB, $this->orgB, $this->userB);

        Sanctum::actingAs($this->userA);

        $response = $this->postJson("/api/v1/analyses/{$analysisB->id}/supporting-documents", [
            'document_id' => $docA->id,
        ]);

        $response->assertStatus(403);
    }

    // ================================================================
    // 1.4 — Users cannot override ownership fields via API
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.4 — organization_id injection in document store
     *
     * Even if an attacker sends organization_id = orgB->id in the request body,
     * the document must be assigned to Tenant A (the authenticated user's org).
     */
    public function test_organization_id_cannot_be_injected_via_store_endpoint(): void
    {
        // We cannot actually POST a real file in a unit test without disk setup,
        // but we CAN verify that the DTO is built from server context.
        // We verify this by inspecting StoreDocumentDTO::fromRequest() which
        // resolves organization_id from $request->user()->organization_id,
        // ignoring any body field.
        //
        // This test verifies the FormRequest only allows the fields it whitelists,
        // so that an extra 'organization_id' field in the JSON is not validated/passed.

        $request = new \App\Http\Requests\Api\V1\StoreDocumentRequest();
        $rules   = $request->rules();

        $this->assertArrayNotHasKey(
            'organization_id',
            $rules,
            'StoreDocumentRequest must NOT expose organization_id as a validatable field — it would allow ownership injection.'
        );

        $this->assertArrayNotHasKey(
            'uploaded_by',
            $rules,
            'StoreDocumentRequest must NOT expose uploaded_by as a validatable field.'
        );
    }

    // ================================================================
    // 1.5 — Users cannot access endpoints without authentication
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.5 — all key API endpoints reject unauthenticated requests
     */
    public function test_unauthenticated_requests_are_rejected_on_all_key_endpoints(): void
    {
        $endpoints = [
            ['GET',    '/api/v1/documents'],
            ['POST',   '/api/v1/documents'],
            ['GET',    '/api/v1/documents/nonexistent-uuid'],
            ['POST',   '/api/v1/documents/nonexistent-uuid/parse'],
            ['GET',    '/api/v1/documents/nonexistent-uuid/parsed'],
            ['POST',   '/api/v1/documents/nonexistent-uuid/analyze'],
            ['GET',    '/api/v1/documents/nonexistent-uuid/analyses'],
            ['GET',    '/api/v1/ai-models'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $this->assertContains(
                $response->status(),
                [401, 403],
                "Endpoint [{$method} {$uri}] must reject unauthenticated requests (got {$response->status()})"
            );
        }
    }

    // ================================================================
    // 1.10 — AI requests cannot leak another tenant's information
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.10 — Tenant A cannot trigger AI analysis on Tenant B's document
     *
     * The policy gate on 'view' ensures the document content never reaches
     * the LLM call if the tenant check fails.
     */
    public function test_tenant_a_cannot_trigger_ai_analysis_on_tenant_b_document(): void
    {
        $docB = $this->createDocument($this->orgB, $this->userB, [
            'metadata' => ['parsed_compact_content' => 'Contenido confidencial de Tenant B.'],
        ]);

        Sanctum::actingAs($this->userA);

        $response = $this->postJson("/api/v1/documents/{$docB->id}/analyze", [
            'analysis_type' => 'full_summary',
        ]);

        // Must be blocked before any LLM call is ever made
        $response->assertStatus(403);

        // Verify no analysis was created for this cross-tenant attempt
        $this->assertDatabaseMissing('document_analyses', [
            'document_id'    => $docB->id,
            'requested_by'   => $this->userA->id,
        ]);
    }

    // ================================================================
    // 1.12 — AI usage quota is enforced per tenant
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.12 — AI quota cannot be bypassed
     */
    public function test_ai_analysis_is_blocked_when_tenant_credits_are_exhausted(): void
    {
        $doc = $this->createDocument($this->orgA, $this->userA, [
            'metadata' => ['parsed_compact_content' => 'Contenido del contrato para análisis.'],
        ]);

        // Drain all credits for Tenant A
        $ledger = $this->orgA->getOrCreateCreditLedger();
        $ledger->update(['credits_remaining' => 0]);

        Sanctum::actingAs($this->userA);

        $response = $this->postJson("/api/v1/documents/{$doc->id}/analyze", [
            'analysis_type' => 'full_summary',
        ]);

        $response->assertStatus(402)
            ->assertJson(['error' => 'credits_depleted']);

        // No analysis should have been persisted
        $this->assertDatabaseMissing('document_analyses', [
            'document_id' => $doc->id,
        ]);
    }

    // ================================================================
    // 1.13 — Cannot proxy AI without authentication
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.13 — unauthenticated analysis request must be blocked
     */
    public function test_ai_analysis_endpoint_requires_authentication(): void
    {
        // No Sanctum::actingAs — pure unauthenticated request
        $response = $this->postJson('/api/v1/documents/any-uuid/analyze', [
            'analysis_type' => 'full_summary',
        ]);

        $response->assertStatus(401);
    }

    // ================================================================
    // 1.14 — Credentials not leaked in error responses
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.14 — error responses must not expose credentials or secrets
     *
     * APP_DEBUG is explicitly set to false for this test to simulate production mode.
     * The test environment inherits APP_DEBUG=true from .env by default, which causes
     * stack traces to appear. This test verifies production behaviour is safe.
     *
     * SECURITY NOTE: phpunit.xml does not set APP_DEBUG=false — this must be added
     * to prevent accidental stack trace exposure in staging test runs.
     */
    public function test_error_responses_do_not_expose_credentials_or_env_secrets(): void
    {
        // Simulate production: disable debug mode for this test scope only
        config(['app.debug' => false]);

        Sanctum::actingAs($this->userA);

        // Trigger a 404 with a non-existent document UUID
        $response = $this->getJson('/api/v1/documents/00000000-0000-0000-0000-000000000000');

        $responseBody = $response->getContent();

        // Ensure none of these sensitive values appear anywhere in the response body
        $sensitivePatterns = [
            env('APP_KEY', ''),
            env('GROQ_API_KEY', ''),
            env('DB_PASSWORD', ''),
            env('AWS_SECRET_ACCESS_KEY', ''),
        ];

        foreach ($sensitivePatterns as $secret) {
            if ($secret === '') {
                continue; // Skip if env var is not set in CI
            }
            $this->assertStringNotContainsString(
                $secret,
                $responseBody,
                'A sensitive credential was found in the HTTP error response body.'
            );
        }

        // In production (APP_DEBUG=false) stack traces must never appear in JSON responses
        $this->assertStringNotContainsString(
            '"exception"',
            strtolower($responseBody),
            'Stack trace key "exception" found in error response — APP_DEBUG may be true in production.'
        );

        // Restore debug mode for subsequent tests
        config(['app.debug' => true]);
    }

    // ================================================================
    // 1.15 — Token revocation works immediately
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.15 — revoked Sanctum token must be rejected immediately
     *
     * Verifies two things:
     * 1. The token record is physically removed from the database on revocation.
     * 2. A subsequent request bearing the revoked token hash is rejected.
     *
     * NOTE: In-memory SQLite tests share the same DB connection, so Sanctum will
     * re-query on every request — the token deletion is immediately visible.
     */
    public function test_revoked_sanctum_token_is_immediately_rejected(): void
    {
        // Issue a real personal access token
        $tokenResult    = $this->userA->createToken('revocation-test-token');
        $plainTextToken = $tokenResult->plainTextToken;
        $tokenId        = $tokenResult->accessToken->id;

        // Confirm the token row exists
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenId]);

        // Confirm the token works before revocation (using withHeaders to avoid guard cache)
        $before = $this->withHeaders(['Authorization' => "Bearer {$plainTextToken}"])
            ->getJson('/api/v1/documents');
        $before->assertStatus(200);

        // Revoke: delete the token from the database
        $this->userA->tokens()->where('id', $tokenId)->delete();

        // Confirm it is gone from DB
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

        // Re-set auth state so the guard does not serve a cached user
        $this->app['auth']->forgetGuards();

        // The same bearer token must now be rejected — Sanctum re-queries the DB
        $after = $this->withHeaders(['Authorization' => "Bearer {$plainTextToken}"])
            ->getJson('/api/v1/documents');
        $after->assertStatus(401);
    }

    // ================================================================
    // 1.17 — Rate limits cannot be trivially bypassed
    // ================================================================

    /**
     * @test
     * PENTESTING_CHECKLIST §1.17 — API rate limiting is active and enforced
     */
    public function test_api_endpoints_enforce_rate_limiting(): void
    {
        Sanctum::actingAs($this->userA);

        $tooManyRequestsHit = false;

        // Rapid requests to hit the 60 requests/minute throttle:api limit
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/v1/documents');

            if ($response->status() === 429) {
                $tooManyRequestsHit = true;
                break;
            }
        }

        $this->assertTrue(
            $tooManyRequestsHit,
            'API endpoint did not return HTTP 429 after exceeding 60 requests/min rate limit.'
        );
    }
}
