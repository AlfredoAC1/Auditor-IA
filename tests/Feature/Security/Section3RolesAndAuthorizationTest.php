<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Actions\Admin\DeleteUserAction;
use App\Actions\Admin\UpdateUserAction;
use App\DTOs\Admin\UpdateUserDTO;
use App\Enums\DocumentStatus;
use App\Enums\UserRole;
use App\Models\AiModel;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AiModelSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Security Test Suite — Section 3: Roles & Authorization Policies
 *
 * Validates all 20 controls from PENTESTING_CHECKLIST.md §3:
 * - §3.1 Role Hierarchy Matrix (10 controls)
 * - §3.2 Escalation & Bypass Tests (10 controls)
 *
 * @see PENTESTING_CHECKLIST.md §3
 */
class Section3RolesAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Organization $orgA;
    private User $userA;
    private Organization $orgB;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(PlanSeeder::class);
        $this->seed(AiModelSeeder::class);

        // System Administrator
        $adminOrg = Organization::create([
            'name'      => 'Platform Ops',
            'slug'      => 'platform-ops',
            'is_active' => true,
        ]);
        $this->adminUser = User::create([
            'name'            => 'System Admin',
            'email'           => 'admin@auditor-ia.local',
            'password'        => bcrypt('AdminSecurePass123!'),
            'role'            => UserRole::Admin,
            'organization_id' => $adminOrg->id,
        ]);

        // Tenant A (Regular User)
        $this->orgA = Organization::create([
            'name'      => 'Tenant A Corp',
            'slug'      => 'tenant-a-corp',
            'is_active' => true,
        ]);
        $this->userA = User::create([
            'name'            => 'Alice User',
            'email'           => 'alice@tenant-a.com',
            'password'        => bcrypt('UserSecurePass123!'),
            'role'            => UserRole::User,
            'organization_id' => $this->orgA->id,
        ]);

        // Tenant B (Victim User)
        $this->orgB = Organization::create([
            'name'      => 'Tenant B Corp',
            'slug'      => 'tenant-b-corp',
            'is_active' => true,
        ]);
        $this->userB = User::create([
            'name'            => 'Bob User',
            'email'           => 'bob@tenant-b.com',
            'password'        => bcrypt('UserSecurePass123!'),
            'role'            => UserRole::User,
            'organization_id' => $this->orgB->id,
        ]);
    }

    private function createDocument(Organization $org, User $user): Document
    {
        return Document::create([
            'organization_id'   => $org->id,
            'uploaded_by'       => $user->id,
            'original_filename' => 'contrato.pdf',
            'stored_filename'   => 'stored-' . uniqid() . '.pdf',
            'storage_path'      => "documents/{$org->id}/2026/09",
            'disk'              => 'local',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 102400,
            'file_hash'         => hash('sha256', uniqid()),
            'status'            => DocumentStatus::Processed,
            'metadata'          => ['parsed_compact_content' => 'Contenido confidencial.'],
        ]);
    }

    // ================================================================
    // 3.1 Role Hierarchy Matrix
    // ================================================================

    /**
     * @test
     * 3.1: Administrative endpoints are accessible by Administrator.
     */
    public function test_administrator_can_access_admin_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');
        $response->assertStatus(200);

        $usersResponse = $this->get('/admin/users');
        $usersResponse->assertStatus(200);

        $orgsResponse = $this->get('/admin/organizations');
        $orgsResponse->assertStatus(200);

        $analysesResponse = $this->get('/admin/analyses');
        $analysesResponse->assertStatus(200);

        $ticketsResponse = $this->get('/admin/tickets');
        $ticketsResponse->assertStatus(200);
    }

    /**
     * @test
     * 3.1: Administrative user management is strictly forbidden to Regular Users.
     */
    public function test_regular_user_cannot_access_user_management(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/admin/users');
        $response->assertStatus(403);

        $createResponse = $this->get('/admin/users/create');
        $createResponse->assertStatus(403);

        $storeResponse = $this->post('/admin/users', [
            'name'     => 'Hacked User',
            'email'    => 'hacked@tenant-a.com',
            'password' => 'password123',
            'role'     => 'admin',
        ]);
        $storeResponse->assertStatus(403);
    }

    /**
     * @test
     * 3.1: Tenant settings / organization toggle is reserved to Administrator.
     */
    public function test_regular_user_cannot_toggle_organization_status(): void
    {
        $this->actingAs($this->userA);

        $response = $this->post("/admin/organizations/{$this->orgA->id}/toggle");
        $response->assertStatus(403);

        // Admin can toggle organization status
        $this->actingAs($this->adminUser);
        $adminResponse = $this->post("/admin/organizations/{$this->orgA->id}/toggle");
        $adminResponse->assertStatus(302); // Redirect back on success
    }

    /**
     * @test
     * 3.1: Analysis and audit permissions — Admin can view all analyses, User only within tenant.
     */
    public function test_analysis_permissions_matrix(): void
    {
        $docB = $this->createDocument($this->orgB, $this->userB);
        $analysisB = DocumentAnalysis::create([
            'document_id'     => $docB->id,
            'organization_id' => $this->orgB->id,
            'requested_by'    => $this->userB->id,
            'analysis_type'   => 'full_summary',
            'llm_provider'    => 'groq',
            'llm_model'       => 'llama-8b-instant',
            'content'         => ['summary' => 'Audit B'],
            'status'          => DocumentStatus::Processed,
            'version'         => 1,
        ]);

        // Regular User A cannot access Tenant B analysis
        Sanctum::actingAs($this->userA);
        $userResponse = $this->getJson("/api/v1/documents/{$docB->id}/analyses/{$analysisB->id}");
        $userResponse->assertStatus(403);

        // Admin can audit analysis across tenants via Admin panel
        $this->actingAs($this->adminUser);
        $adminResponse = $this->get("/admin/analyses/{$analysisB->id}");
        $adminResponse->assertStatus(200);
    }

    /**
     * @test
     * 3.1: Billing quota permissions — Users cannot bypass AI credit limits.
     */
    public function test_user_cannot_bypass_ai_credit_quota(): void
    {
        $docA = $this->createDocument($this->orgA, $this->userA);

        // Drain credits for Tenant A
        $ledger = $this->orgA->getOrCreateCreditLedger();
        $ledger->update(['credits_remaining' => 0]);

        Sanctum::actingAs($this->userA);

        $response = $this->postJson("/api/v1/documents/{$docA->id}/analyze", [
            'analysis_type' => 'full_summary',
        ]);

        $response->assertStatus(402)
            ->assertJson(['error' => 'credits_depleted']);
    }

    // ================================================================
    // 3.2 Escalation & Bypass Tests
    // ================================================================

    /**
     * @test
     * 3.2: Vertical privilege escalation — Regular User cannot access any /admin endpoint.
     */
    public function test_vertical_privilege_escalation_is_blocked_on_all_admin_routes(): void
    {
        $this->actingAs($this->userA);

        $adminRoutes = [
            ['GET', '/admin'],
            ['GET', '/admin/users'],
            ['GET', '/admin/organizations'],
            ['GET', '/admin/analyses'],
            ['GET', '/admin/tickets'],
        ];

        foreach ($adminRoutes as [$method, $uri]) {
            $response = $this->call($method, $uri);
            $response->assertStatus(403);
        }
    }

    /**
     * @test
     * 3.2: Unauthenticated guests are redirected or rejected from admin endpoints.
     */
    public function test_unauthenticated_guests_cannot_access_admin_endpoints(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');

        $usersResponse = $this->get('/admin/users');
        $usersResponse->assertRedirect('/login');
    }

    /**
     * @test
     * 3.2: Role manipulation — Client cannot self-promote to 'admin' during registration.
     */
    public function test_role_manipulation_blocked_during_registration(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Attacker Registrant',
            'email'                 => 'attacker.reg@example.com',
            'password'              => 'StrongPass1234!',
            'password_confirmation' => 'StrongPass1234!',
            'organization_name'     => 'Injected Org',
            'role'                  => 'admin',
            'is_admin'              => true,
            'is_owner'              => true,
            'terms'                 => '1',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'attacker.reg@example.com')->firstOrFail();

        // User must be created strictly as a regular User, never Admin
        $this->assertEquals(UserRole::User, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    /**
     * @test
     * 3.2: Role manipulation — Client cannot inject 'role', 'is_admin', or 'is_owner' in profile update.
     */
    public function test_role_manipulation_blocked_during_profile_update(): void
    {
        $this->actingAs($this->userA);

        $response = $this->post('/configuracion/profile', [
            'name'     => 'Alice Modified',
            'role'     => 'admin',
            'is_admin' => true,
            'is_owner' => true,
        ]);

        $response->assertRedirect('/configuracion');

        $this->userA->refresh();

        $this->assertEquals('Alice Modified', $this->userA->name);
        $this->assertEquals(UserRole::User, $this->userA->role);
        $this->assertFalse($this->userA->isAdmin());
    }

    /**
     * @test
     * 3.2: Horizontal privilege escalation — User A cannot access or update User B's settings.
     */
    public function test_horizontal_privilege_escalation_blocked_on_avatar(): void
    {
        $this->actingAs($this->userA);

        // User A attempts to view User B's private avatar
        $response = $this->get("/configuracion/avatar/{$this->userB->id}");
        $response->assertStatus(403);
    }

    /**
     * @test
     * 3.2: Admin Action Safety — Administrator cannot delete their own account.
     */
    public function test_admin_cannot_delete_own_account(): void
    {
        $deleteAction = new DeleteUserAction();

        $this->expectException(ValidationException::class);

        $deleteAction->execute($this->adminUser, $this->adminUser);
    }

    /**
     * @test
     * 3.2: Admin Action Safety — System prevents demoting the last active administrator.
     */
    public function test_cannot_demote_the_last_active_administrator(): void
    {
        $updateAction = new UpdateUserAction();

        $dto = new UpdateUserDTO(
            name: $this->adminUser->name,
            email: $this->adminUser->email,
            role: UserRole::User, // Attempt to demote sole admin
            organizationId: $this->adminUser->organization_id,
        );

        $this->expectException(ValidationException::class);

        $updateAction->execute($this->adminUser, $dto, $this->adminUser);
    }

    /**
     * @test
     * 3.2: Admin Action Safety — System prevents deleting the last active administrator.
     */
    public function test_cannot_delete_the_last_active_administrator(): void
    {
        // Create a secondary admin who attempts to delete the last remaining other admin
        $secondAdmin = User::create([
            'name'            => 'Second Admin',
            'email'           => 'second.admin@auditor-ia.local',
            'password'        => bcrypt('AdminSecurePass123!'),
            'role'            => UserRole::Admin,
            'organization_id' => $this->adminUser->organization_id,
        ]);

        $deleteAction = new DeleteUserAction();

        // Second admin deletes first admin -> succeeds because secondAdmin remains
        $deleteAction->execute($this->adminUser, $secondAdmin);
        $this->assertDatabaseMissing('users', ['id' => $this->adminUser->id]);

        // Now secondAdmin is the sole admin. Another actor cannot delete secondAdmin.
        $this->expectException(ValidationException::class);
        $deleteAction->execute($secondAdmin, $this->userA);
    }
}
