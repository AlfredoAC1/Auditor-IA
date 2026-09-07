<?php

declare(strict_types=1);

namespace Tests\Feature\BugTickets;

use App\Enums\BugTicketStatus;
use App\Enums\UserRole;
use App\Models\BugTicket;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BugTicketTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $this->org = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Demo Law Firm',
            'slug' => 'demo-law-firm',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Abogado Juan',
            'email' => 'juan@law.cl',
            'password' => 'password123',
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@auditor.cl',
            'password' => 'password123',
            'role' => UserRole::Admin,
            'organization_id' => $this->org->id,
        ]);
    }

    public function test_user_can_submit_bug_ticket(): void
    {
        $response = $this->actingAs($this->user)->post(route('tickets.store'), [
            'title' => 'Fallo al descargar reporte en PDF',
            'description' => 'Al hacer clic en el botón de exportar análisis a PDF, la página muestra un error 500.',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bug_tickets', [
            'user_id' => $this->user->id,
            'organization_id' => $this->org->id,
            'title' => 'Fallo al descargar reporte en PDF',
            'status' => BugTicketStatus::New->value,
        ]);
    }

    public function test_admin_can_view_bug_tickets_list(): void
    {
        BugTicket::create([
            'user_id' => $this->user->id,
            'organization_id' => $this->org->id,
            'title' => 'Error en visualización de cláusulas',
            'description' => 'Las cláusulas no cargan en la pantalla de detalle.',
            'status' => BugTicketStatus::New,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.tickets.index'));

        $response->assertOk();
        $response->assertSee('Error en visualización de cláusulas');
        $response->assertSee('Abogado Juan');
    }

    public function test_admin_can_resolve_bug_ticket(): void
    {
        $ticket = BugTicket::create([
            'user_id' => $this->user->id,
            'organization_id' => $this->org->id,
            'title' => 'Problema con subida de archivo',
            'description' => 'El archivo DOCX arrojó error de formato.',
            'status' => BugTicketStatus::New,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.tickets.resolve', $ticket->id));

        $response->assertRedirect(route('admin.tickets.index'));
        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame(BugTicketStatus::Resolved, $ticket->status);
        $this->assertEquals($this->admin->id, $ticket->resolved_by);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_non_admin_cannot_resolve_bug_ticket(): void
    {
        $ticket = BugTicket::create([
            'user_id' => $this->user->id,
            'organization_id' => $this->org->id,
            'title' => 'Error leve',
            'description' => 'Texto descriptivo',
            'status' => BugTicketStatus::New,
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.tickets.resolve', $ticket->id));

        $response->assertForbidden();
        $ticket->refresh();
        $this->assertSame(BugTicketStatus::New, $ticket->status);
    }
}
