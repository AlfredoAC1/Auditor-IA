<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Document;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentParsingControllerTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_can_parse_document_successfully(): void
    {
        Storage::fake('local');
        
        $document = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'contrato.pdf',
            'stored_filename' => 'fake-uuid.pdf',
            'storage_path' => 'documents/1/2026/06',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => 'fakehash123',
            'status' => 'pending',
        ]);

        Storage::disk('local')->put($document->fullStoragePath(), 'Fake PDF Content');

        // Mock Unstructured API response
        Http::fake([
            'http://localhost:8000/*' => Http::response([
                [
                    'type' => 'Title',
                    'element_id' => 'elem1',
                    'text' => 'CONTRATO DE ARRENDAMIENTO',
                    'metadata' => ['page_number' => 1],
                ],
                [
                    'type' => 'NarrativeText',
                    'element_id' => 'elem2',
                    'text' => 'En Santiago de Chile, a 9 de Junio de 2026...',
                    'metadata' => ['page_number' => 1],
                ]
            ], 200),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/documents/{$document->id}/parse");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'document_id',
                'element_count',
                'estimated_tokens',
                'compact_content_preview',
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
        ]);

        $updatedDocument = Document::find($document->id);
        $this->assertNotNull($updatedDocument->metadata);
        $this->assertArrayHasKey('parsed_elements', $updatedDocument->metadata);
        $this->assertEquals(2, $updatedDocument->metadata['parsed_element_count']);
    }

    public function test_cannot_parse_non_existent_file(): void
    {
        $document = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'contrato.pdf',
            'stored_filename' => 'fake-uuid.pdf',
            'storage_path' => 'documents/1/2026/06',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => 'fakehash123',
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/documents/{$document->id}/parse");

        $response->assertStatus(503)
            ->assertJson([
                'error' => 'parsing_failed',
            ]);
    }
}
