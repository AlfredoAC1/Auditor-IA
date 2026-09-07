<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\AiModelSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentAnalysisControllerTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Enforce LLM_PROVIDER as groq and set a dummy key for testing
        config(['llm.providers.groq.api_key' => 'fake-groq-key']);

        $this->seed(PlanSeeder::class);
        $this->seed(AiModelSeeder::class);

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

    public function test_can_analyze_document_successfully(): void
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
            'metadata' => [
                'parsed_compact_content' => 'Texto del contrato a ser analizado...',
                'parsed_elements' => [['type' => 'NarrativeText', 'text' => 'Texto del contrato...']],
            ],
        ]);

        Http::fake([
            'https://api.cerebras.ai/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_general' => 'Contrato de arrendamiento estándar de prueba.',
                                'tipo_documento' => 'Contrato de Arrendamiento',
                                'nivel_confianza' => 'alto',
                                'leyes_aplicables' => ['Ley 18.101'],
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 200,
                    'completion_tokens' => 150,
                ],
            ], 200),
            'https://api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'resumen_general' => 'Contrato de arrendamiento estándar de prueba.',
                                'tipo_documento' => 'Contrato de Arrendamiento',
                                'nivel_confianza' => 'alto',
                                'leyes_aplicables' => ['Ley 18.101'],
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 200,
                    'completion_tokens' => 150,
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->user);

        $ledger = $this->organization->getOrCreateCreditLedger();
        $initialBalance = $ledger->credits_remaining;

        $response = $this->postJson("/api/v1/documents/{$document->id}/analyze", [
            'analysis_type' => 'full_summary',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'document_id',
                    'organization_id',
                    'analysis_type',
                    'analysis_type_label',
                    'llm_provider',
                    'llm_model',
                    'content' => [
                        'resumen_general',
                        'tipo_documento',
                        'nivel_confianza',
                        'leyes_aplicables',
                    ],
                    'confidence_score',
                    'input_tokens',
                    'output_tokens',
                    'total_tokens',
                    'processing_time_ms',
                    'status',
                    'status_label',
                    'version',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('document_analyses', [
            'document_id' => $document->id,
            'analysis_type' => 'full_summary',
            'status' => 'processed',
        ]);

        // Assert AI credits were deducted
        $this->assertEquals($initialBalance - 1, $ledger->fresh()->credits_remaining);

        $this->assertDatabaseHas('credit_transactions', [
            'organization_id' => $this->organization->id,
            'type' => 'analysis_debit',
        ]);
    }

    public function test_cannot_analyze_when_credits_depleted(): void
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
            'metadata' => [
                'parsed_compact_content' => 'Texto del contrato...',
            ],
        ]);

        // Deplete credits
        $ledger = $this->organization->getOrCreateCreditLedger();
        $ledger->update(['credits_remaining' => 0]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/documents/{$document->id}/analyze", [
            'analysis_type' => 'full_summary',
        ]);

        $response->assertStatus(402)
            ->assertJson([
                'error' => 'credits_depleted',
            ]);
    }

    public function test_can_list_available_ai_models(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/ai-models');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'display_name',
                        'tier' => ['value', 'label', 'description'],
                        'credit_multiplier',
                        'credit_cost',
                        'max_tokens',
                    ],
                ],
                'meta' => [
                    'allows_model_selection',
                    'credits_remaining',
                    'credits_quota',
                ],
            ]);
    }

    public function test_cannot_analyze_unparsed_document(): void
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
            'metadata' => [], // No parsed content
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/documents/{$document->id}/analyze", [
            'analysis_type' => 'full_summary',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'not_parsed',
            ]);
    }

    public function test_can_list_analyses(): void
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

        DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $this->organization->id,
            'requested_by' => $this->user->id,
            'analysis_type' => 'full_summary',
            'llm_provider' => 'groq',
            'llm_model' => 'llama-8b-instant',
            'content' => ['resumen_general' => 'test'],
            'status' => 'processed',
            'version' => 1,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/documents/{$document->id}/analyses");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'analysis_type',
                        'content',
                    ],
                ],
            ]);
    }
}
