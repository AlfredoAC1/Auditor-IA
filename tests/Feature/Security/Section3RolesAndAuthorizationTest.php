<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\DocumentStatus;
use App\Enums\UserRole;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AiModelSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Security Test Suite — Section 3: Roles and Authorization Policies
 *
 * Covers all 20 controls from PENTESTING_CHECKLIST.md §3:
 * - §3.1 Role Hierarchy Matrix (10 controls)
 * - §3.2 Escalation & Bypass Tests (10 controls)
 *
 * @see PENTESTING_CHECKLIST.md §3
 */
class Section3RolesAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private User $owner;

    private User $admin;

    private User $auditor;

    private User $regularUser;

    private User $anotherUser;

    private Organization $externalOrg;

    private User $externalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->seed(AiModelSeeder::class);

        $this->org = Organization::create([
            'name' => 'Acme Legal Corp',
            'slug' => 'acme-legal',
            'is_active' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Org Owner',
            'email' => 'owner@acme.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => UserRole::Owner,
            'organization_id' => $this->org->id,
        ]);

        $this->admin = User::create([
            'name' => 'Org Admin',
            'email' => 'admin@acme.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => UserRole::Admin,
            'organization_id' => $this->org->id,
        ]);

        $this->auditor = User::create([
            'name' => 'Legal Auditor',
            'email' => 'auditor@acme.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => UserRole::Auditor,
            'organization_id' => $this->org->id,
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User 1',
            'email' => 'user1@acme.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        $this->anotherUser = User::create([
            'name' => 'Regular User 2',
            'email' => 'user2@acme.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        // External organization for horizontal escalation testing
        $this->externalOrg = Organization::create([
            'name' => 'Rival Firm',
            'slug' => 'rival-firm',
            'is_active' => true,
        ]);

        $this->externalUser = User::create([
            'name' => 'External User',
            'email' => 'external@rival.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => UserRole::User,
            'organization_id' => $this->externalOrg->id,
        ]);
    }

    private function createDocument(User $creator, array $extra = []): Document
    {
        return Document::create(array_merge([
            'organization_id' => $creator->organization_id,
            'uploaded_by' => $creator->id,
            'original_filename' => 'contrato_auditoria.pdf',
            'stored_filename' => 'doc-'.uniqid().'.pdf',
            'storage_path' => "documents/{$creator->organization_id}/2026/09",
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 102400,
            'file_hash' => hash('sha256', uniqid()),
            'status' => DocumentStatus::Processed,
            'metadata' => ['parsed_compact_content' => 'Contrato confidencial.'],
        ], $extra));
    }

    private function createAnalysis(Document $document, User $creator): DocumentAnalysis
    {
        return DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $document->organization_id,
            'requested_by' => $creator->id,
            'analysis_type' => 'full_summary',
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['resumen' => 'Auditoría completada.'],
            'status' => 'processed',
            'version' => 1,
        ]);
    }

    // ================================================================
    // 3.1 Role Hierarchy Matrix Tests
    // ================================================================

    /**
     * PENTESTING_CHECKLIST §3.1 — Document permissions across roles
     */
    public function test_document_permissions_matrix_across_roles(): void
    {
        $docOwnedByOwner = $this->createDocument($this->owner);
        $docOwnedByUser1 = $this->createDocument($this->regularUser);

        // 1. Owner: can view, create, and delete any doc in org
        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $docOwnedByUser1));
        $this->assertTrue(Gate::forUser($this->owner)->allows('create', Document::class));
        $this->assertTrue(Gate::forUser($this->owner)->allows('delete', $docOwnedByUser1));

        // 2. Admin: can view, create, and delete any doc in org
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $docOwnedByOwner));
        $this->assertTrue(Gate::forUser($this->admin)->allows('create', Document::class));
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $docOwnedByUser1));

        // 3. Auditor: can view, but CANNOT create or delete documents (preserves audit integrity)
        $this->assertTrue(Gate::forUser($this->auditor)->allows('view', $docOwnedByOwner));
        $this->assertFalse(Gate::forUser($this->auditor)->allows('create', Document::class));
        $this->assertFalse(Gate::forUser($this->auditor)->allows('delete', $docOwnedByOwner));
        $this->assertFalse(Gate::forUser($this->auditor)->allows('delete', $docOwnedByUser1));

        // 4. Regular User: can view org docs, create, delete own, but CANNOT delete other users' docs
        $this->assertTrue(Gate::forUser($this->regularUser)->allows('view', $docOwnedByOwner));
        $this->assertTrue(Gate::forUser($this->regularUser)->allows('create', Document::class));
        $this->assertTrue(Gate::forUser($this->regularUser)->allows('delete', $docOwnedByUser1));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('delete', $docOwnedByOwner));
        $this->assertFalse(Gate::forUser($this->anotherUser)->allows('delete', $docOwnedByUser1));
    }

    /**
     * PENTESTING_CHECKLIST §3.1 — Analysis permissions across roles
     */
    public function test_analysis_permissions_matrix_across_roles(): void
    {
        $doc = $this->createDocument($this->owner);
        $analysis = $this->createAnalysis($doc, $this->owner);

        // All org roles can view analyses
        $this->assertTrue(Gate::forUser($this->owner)->allows('view', $analysis));
        $this->assertTrue(Gate::forUser($this->admin)->allows('view', $analysis));
        $this->assertTrue(Gate::forUser($this->auditor)->allows('view', $analysis));
        $this->assertTrue(Gate::forUser($this->regularUser)->allows('view', $analysis));

        // Only Owner and Admin can delete analyses; Auditor and User cannot delete
        $this->assertTrue(Gate::forUser($this->owner)->allows('delete', $analysis));
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $analysis));
        $this->assertFalse(Gate::forUser($this->auditor)->allows('delete', $analysis));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('delete', $analysis));
    }

    /**
     * PENTESTING_CHECKLIST §3.1 — User-management permissions across roles
     */
    public function test_user_management_permissions_matrix_across_roles(): void
    {
        // Owner and Admin can view and create users
        $this->assertTrue(Gate::forUser($this->owner)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($this->admin)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($this->owner)->allows('create', User::class));
        $this->assertTrue(Gate::forUser($this->admin)->allows('create', User::class));

        // Auditor and User cannot manage users
        $this->assertFalse(Gate::forUser($this->auditor)->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser($this->auditor)->allows('create', User::class));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('create', User::class));
    }

    /**
     * PENTESTING_CHECKLIST §3.1 — Billing permissions restricted to Owner and System Admin
     */
    public function test_billing_permissions_matrix_across_roles(): void
    {
        $this->assertTrue($this->owner->canManageBilling());
        $this->assertTrue(Gate::forUser($this->owner)->allows('manageBilling', $this->org));

        $this->assertFalse($this->admin->canManageBilling());
        $this->assertFalse($this->auditor->canManageBilling());
        $this->assertFalse($this->regularUser->canManageBilling());

        $this->assertFalse(Gate::forUser($this->auditor)->allows('manageBilling', $this->org));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('manageBilling', $this->org));
    }

    /**
     * PENTESTING_CHECKLIST §3.1 — Tenant settings permissions matrix
     */
    public function test_tenant_settings_permissions_matrix(): void
    {
        $this->assertTrue($this->owner->canManageSettings());
        $this->assertTrue($this->admin->canManageSettings());
        $this->assertTrue(Gate::forUser($this->owner)->allows('update', $this->org));
        $this->assertTrue(Gate::forUser($this->admin)->allows('update', $this->org));

        $this->assertFalse($this->auditor->canManageSettings());
        $this->assertFalse($this->regularUser->canManageSettings());
        $this->assertFalse(Gate::forUser($this->auditor)->allows('update', $this->org));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('update', $this->org));
    }

    /**
     * PENTESTING_CHECKLIST §3.1 — Administrative panel endpoint access
     */
    public function test_administrative_endpoints_restrict_non_admins(): void
    {
        // System Admin can access admin dashboard
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();

        // Owner, Auditor, and Regular User must receive 403 Forbidden
        $this->actingAs($this->owner)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->auditor)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->regularUser)->get(route('admin.dashboard'))->assertForbidden();
    }

    // ================================================================
    // 3.2 Escalation & Bypass Tests
    // ================================================================

    /**
     * PENTESTING_CHECKLIST §3.2 — Horizontal privilege escalation tested
     */
    public function test_horizontal_privilege_escalation_is_blocked(): void
    {
        $externalDoc = $this->createDocument($this->externalUser);

        // Regular user from Acme cannot read or delete Rival Firm document
        Sanctum::actingAs($this->regularUser);
        $this->getJson("/api/v1/documents/{$externalDoc->id}")->assertForbidden();

        $this->assertFalse(Gate::forUser($this->regularUser)->allows('view', $externalDoc));
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('delete', $externalDoc));
    }

    /**
     * PENTESTING_CHECKLIST §3.2 — Vertical privilege escalation tested
     */
    public function test_vertical_privilege_escalation_is_blocked(): void
    {
        // Regular user cannot promote self or access admin routes
        $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($this->regularUser)
            ->post(route('admin.users.store'), [
                'name' => 'Escalated',
                'email' => 'escalated@acme.com',
                'password' => 'SecurePass123!',
                'role' => 'admin',
            ])
            ->assertForbidden();
    }

    /**
     * PENTESTING_CHECKLIST §3.2 — Role manipulation via profile update is blocked
     */
    public function test_role_manipulation_via_profile_update_is_ignored(): void
    {
        $this->actingAs($this->regularUser)
            ->post(route('settings.profile'), [
                'name' => 'Hacked Name',
                'role' => 'admin',
            ]);

        $this->regularUser->refresh();
        $this->assertSame(UserRole::User, $this->regularUser->role);
        $this->assertSame('Hacked Name', $this->regularUser->name);
    }

    /**
     * PENTESTING_CHECKLIST §3.2 — is_admin and is_owner field manipulation
     */
    public function test_is_admin_and_is_owner_field_manipulation_is_ignored(): void
    {
        $this->actingAs($this->regularUser)
            ->post(route('settings.profile'), [
                'name' => 'Attacker',
                'is_admin' => true,
                'is_owner' => true,
            ]);

        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->isAdmin());
        $this->assertFalse($this->regularUser->isOwner());
    }

    /**
     * PENTESTING_CHECKLIST §3.2 — tenant_id / organization_id manipulation is blocked
     */
    public function test_tenant_id_manipulation_is_ignored_in_profile_and_actions(): void
    {
        $this->actingAs($this->regularUser)
            ->post(route('settings.profile'), [
                'name' => 'Attacker',
                'organization_id' => $this->externalOrg->id,
            ]);

        $this->regularUser->refresh();
        $this->assertSame($this->org->id, $this->regularUser->organization_id);
    }

    /**
     * PENTESTING_CHECKLIST §3.2 — Direct API requests to non-UI actions are blocked
     */
    public function test_direct_api_requests_to_admin_toggle_by_non_admin_are_blocked(): void
    {
        $this->actingAs($this->regularUser)
            ->post(route('admin.organizations.toggle', $this->org))
            ->assertForbidden();

        $this->actingAs($this->owner)
            ->post(route('admin.organizations.toggle', $this->org))
            ->assertForbidden();
    }

    /**
     * PENTESTING_CHECKLIST §3.2 — Owner cannot be deleted by non-admin or non-owner
     */
    public function test_owner_cannot_be_deleted_by_regular_user_or_auditor(): void
    {
        $this->assertFalse(Gate::forUser($this->regularUser)->allows('delete', $this->owner));
        $this->assertFalse(Gate::forUser($this->auditor)->allows('delete', $this->owner));
        $this->assertFalse(Gate::forUser($this->owner)->allows('delete', $this->owner)); // self-deletion blocked
    }
}
