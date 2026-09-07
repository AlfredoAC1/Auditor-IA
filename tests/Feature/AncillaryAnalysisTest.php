<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\Documents\ProcessAncillaryAnalysisJob;
use App\Models\AiModel;
use App\Models\AnalysisSupportingDocument;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Billing\AiCreditService;
use App\Services\Documents\DocumentParsingService;
use App\Services\LLM\LlmAnalysisService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AncillaryAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private Organization $proOrg;
    private User $proUser;
    private Document $primaryDocument;
    private DocumentAnalysis $primaryAnalysis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        // Create active AI models for testing
        AiModel::firstOrCreate(
            ['slug' => 'cerebras-gemma4'],
            [
                'display_name' => 'Cerebras Gemma 4',
                'provider' => 'cerebras',
                'provider_model_id' => 'gemma-4-31b',
                'tier' => 'standard',
                'credit_multiplier' => 1.0,
                'max_tokens' => 8192,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();
        $this->proOrg = Organization::create([
            'name' => 'Pro Legal Org',
            'slug' => 'pro-legal-org',
            'is_active' => true,
        ]);

        Subscription::create([
            'organization_id' => $this->proOrg->id,
            'plan_id' => $proPlan->id,
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        $this->proUser = User::factory()->create([
            'organization_id' => $this->proOrg->id,
        ]);

        $this->primaryDocument = Document::create([
            'organization_id'   => $this->proOrg->id,
            'uploaded_by'       => $this->proUser->id,
            'original_filename' => 'contrato_prestacion_servicios.pdf',
            'stored_filename'   => 'stored-primary-uuid.pdf',
            'storage_path'      => "documents/{$this->proOrg->id}/2026/09",
            'disk'              => 'local',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 2048,
            'file_hash'         => 'primaryhash123',
            'status'            => DocumentStatus::Processed,
            'metadata'          => [
                'parsed_compact_content' => 'Texto del contrato principal de prestacion de servicios.',
            ],
        ]);

        $this->primaryAnalysis = DocumentAnalysis::create([
            'document_id'        => $this->primaryDocument->id,
            'organization_id'    => $this->proOrg->id,
            'requested_by'       => $this->proUser->id,
            'analysis_type'      => AnalysisType::FullSummary,
            'llm_provider'       => 'cerebras',
            'llm_model'          => 'cerebras-gemma4',
            'content'            => [
                'resumen_general' => 'Resumen del contrato principal.',
                'tipo_documento'  => 'Contrato de Prestación de Servicios',
            ],
            'confidence_score'   => 0.85,
            'input_tokens'       => 500,
            'output_tokens'      => 200,
            'processing_time_ms' => 1200,
            'status'             => DocumentStatus::Processed,
            'version'            => 1,
        ]);
    }

    public function test_non_pro_user_cannot_upload_ancillary_document(): void
    {
        $freeOrg = Organization::create([
            'name' => 'Free Org',
            'slug' => 'free-org-test',
            'is_active' => true,
        ]);

        $freeUser = User::factory()->create([
            'organization_id' => $freeOrg->id,
        ]);

        $freeDoc = Document::create([
            'organization_id'   => $freeOrg->id,
            'uploaded_by'       => $freeUser->id,
            'original_filename' => 'free_doc.pdf',
            'stored_filename'   => 'stored-free.pdf',
            'storage_path'      => "documents/{$freeOrg->id}/2026/09",
            'disk'              => 'local',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 1024,
            'file_hash'         => 'freehash123',
            'status'            => DocumentStatus::Processed,
        ]);

        $freeAnalysis = DocumentAnalysis::create([
            'document_id'     => $freeDoc->id,
            'organization_id' => $freeOrg->id,
            'requested_by'    => $freeUser->id,
            'analysis_type'   => AnalysisType::FullSummary,
            'llm_provider'    => 'cerebras',
            'llm_model'       => 'cerebras-gemma4',
            'content'         => ['resumen_general' => 'Test'],
            'status'          => DocumentStatus::Processed,
            'version'         => 1,
        ]);

        $file = UploadedFile::fake()->create('adenda.pdf', 500, 'application/pdf');

        $response = $this->actingAs($freeUser)->post(
            route('analysis.ancillary.upload', $freeAnalysis),
            [
                'file'              => $file,
                'relationship_type' => 'amendment',
            ]
        );

        $response->assertForbidden();
    }

    public function test_user_cannot_upload_ancillary_to_another_organizations_analysis(): void
    {
        $otherProOrg = Organization::create([
            'name' => 'Other Pro Org',
            'slug' => 'other-pro-org',
            'is_active' => true,
        ]);

        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();
        Subscription::create([
            'organization_id' => $otherProOrg->id,
            'plan_id' => $proPlan->id,
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        $otherUser = User::factory()->create([
            'organization_id' => $otherProOrg->id,
        ]);

        $file = UploadedFile::fake()->create('adenda.pdf', 500, 'application/pdf');

        // Other user attempts to attach to $this->primaryAnalysis (owned by $this->proOrg)
        $response = $this->actingAs($otherUser)->post(
            route('analysis.ancillary.upload', $this->primaryAnalysis),
            [
                'file' => $file,
            ]
        );

        $response->assertForbidden();
    }

    public function test_pro_user_can_upload_ancillary_document_and_queue_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('adenda_modificacion.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->proUser)->post(
            route('analysis.ancillary.upload', $this->primaryAnalysis),
            [
                'file'              => $file,
                'relationship_type' => 'amendment',
                'notes'             => 'Adenda que modifica la cláusula quinta sobre honorarios.',
            ]
        );

        $response->assertStatus(202);
        $response->assertJsonStructure([
            'message',
            'ancillary_document_id',
            'status_url',
        ]);

        $ancillaryDocId = $response->json('ancillary_document_id');

        $this->assertDatabaseHas('documents', [
            'id'                => $ancillaryDocId,
            'organization_id'   => $this->proOrg->id,
            'original_filename' => 'adenda_modificacion.pdf',
            'status'            => DocumentStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('analysis_supporting_documents', [
            'analysis_id'       => $this->primaryAnalysis->id,
            'document_id'       => $ancillaryDocId,
            'organization_id'   => $this->proOrg->id,
            'relationship_type' => 'amendment',
            'notes'             => 'Adenda que modifica la cláusula quinta sobre honorarios.',
        ]);

        Queue::assertPushed(ProcessAncillaryAnalysisJob::class, function ($job) use ($ancillaryDocId) {
            return $job->ancillaryDocument->id === $ancillaryDocId
                && $job->analysis->id === $this->primaryAnalysis->id;
        });
    }

    public function test_ancillary_job_parses_only_ancillary_and_creates_contextual_analysis(): void
    {
        Storage::fake('local');

        config([
            'llm.providers.cerebras.api_key' => 'fake-cerebras-key',
            'documents.unstructured.api_key' => 'fake-unstructured-key',
        ]);

        // Create ancillary document
        $ancillaryDoc = Document::create([
            'organization_id'   => $this->proOrg->id,
            'uploaded_by'       => $this->proUser->id,
            'original_filename' => 'adenda.pdf',
            'stored_filename'   => 'stored-adenda-uuid.pdf',
            'storage_path'      => "documents/{$this->proOrg->id}/2026/09",
            'disk'              => 'local',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 1024,
            'file_hash'         => 'adendahash123',
            'status'            => DocumentStatus::Pending,
            'metadata'          => [
                'is_ancillary'        => true,
                'primary_document_id' => $this->primaryDocument->id,
            ],
        ]);

        Storage::disk('local')->put($ancillaryDoc->fullStoragePath(), 'Fake Ancillary PDF Content');

        // Http::fake for Unstructured API and Cerebras LLM
        \Illuminate\Support\Facades\Http::fake([
            config('documents.unstructured.api_url', '*') => \Illuminate\Support\Facades\Http::response([
                [
                    'type' => 'NarrativeText',
                    'element_id' => 'e1',
                    'text' => 'Contenido de la adenda que amplía el plazo contractual.',
                    'metadata' => ['page_number' => 1],
                ],
            ], 200),

            '*chat/completions*' => \Illuminate\Support\Facades\Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'tipo_documento'        => 'Contrato de Prestación de Servicios',
                                'resumen_integrado'     => 'Resumen integrado entre contrato y adenda.',
                                'datos_complementarios' => [
                                    [
                                        'elemento' => 'Plazo Contractual',
                                        'impacto'  => 'positivo',
                                        'detalle'  => 'Se extiende la vigencia por 6 meses adicionales.',
                                    ],
                                ],
                                'contradicciones' => [],
                                'bonus_insights' => [
                                    'prevalencia_juridica'   => 'La adenda prevalece sobre la cláusula tercera del contrato base.',
                                    'vigencia_y_personerias' => 'Representantes con facultades vigentes.',
                                    'variacion_del_riesgo'   => 'disminuye',
                                    'justificacion_riesgo'   => 'La prórroga reduce el riesgo de incumplimiento por plazo.',
                                ],
                                'nivel_confianza' => 'alto',
                            ]),
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens'     => 800,
                    'completion_tokens' => 350,
                    'total_tokens'      => 1150,
                ],
            ], 200),
        ]);

        $job = new ProcessAncillaryAnalysisJob(
            analysis: $this->primaryAnalysis,
            ancillaryDocument: $ancillaryDoc,
            modelSlug: 'cerebras-gemma4',
        );

        $job->handle(
            parsingService: app(DocumentParsingService::class),
            llmService: app(LlmAnalysisService::class),
            creditService: app(AiCreditService::class),
        );

        // Verify that a new ContextualSummary analysis was created
        $this->assertDatabaseHas('document_analyses', [
            'document_id'     => $this->primaryDocument->id,
            'organization_id' => $this->proOrg->id,
            'analysis_type'   => AnalysisType::ContextualSummary->value,
            'status'          => DocumentStatus::Processed->value,
            'version'         => 1,
        ]);

        $ancillaryDoc->refresh();
        $this->assertEquals(DocumentStatus::Processed, $ancillaryDoc->status);
        $this->assertNotNull($ancillaryDoc->metadata['contextual_analysis_id'] ?? null);
    }

    public function test_can_view_contextual_analysis_page(): void
    {
        $ancillaryDoc = Document::create([
            'organization_id'   => $this->proOrg->id,
            'uploaded_by'       => $this->proUser->id,
            'original_filename' => 'anexo_tecnico.pdf',
            'stored_filename'   => 'stored-anexo.pdf',
            'storage_path'      => "documents/{$this->proOrg->id}/2026/09",
            'disk'              => 'local',
            'mime_type'         => 'application/pdf',
            'file_size_bytes'   => 1024,
            'file_hash'         => 'anexohash123',
            'status'            => DocumentStatus::Processed,
        ]);

        $contextualAnalysis = DocumentAnalysis::create([
            'document_id'        => $this->primaryDocument->id,
            'organization_id'    => $this->proOrg->id,
            'requested_by'       => $this->proUser->id,
            'analysis_type'      => AnalysisType::ContextualSummary,
            'llm_provider'       => 'cerebras',
            'llm_model'          => 'cerebras-gemma4',
            'content'            => [
                'resumen_integrado'     => 'Resumen integrado completo para pruebas.',
                'datos_complementarios' => [
                    [
                        'elemento' => 'Garantía de Fiel Cumplimiento',
                        'impacto'  => 'positivo',
                        'detalle'  => 'Se añade boleta de garantía por 100 UF.',
                    ],
                ],
                'contradicciones' => [],
                'bonus_insights' => [
                    'prevalencia_juridica' => 'El anexo técnico rige en especificaciones operativas.',
                    'variacion_del_riesgo' => 'disminuye',
                    'justificacion_riesgo' => 'Mayor claridad en entregables.',
                ],
                'nivel_confianza' => 'alto',
            ],
            'confidence_score'   => 0.85,
            'input_tokens'       => 600,
            'output_tokens'      => 300,
            'processing_time_ms' => 1400,
            'status'             => DocumentStatus::Processed,
            'version'            => 1,
        ]);

        AnalysisSupportingDocument::create([
            'analysis_id'       => $contextualAnalysis->id,
            'document_id'       => $ancillaryDoc->id,
            'organization_id'   => $this->proOrg->id,
            'attached_by'       => $this->proUser->id,
            'relationship_type' => 'technical_annex',
        ]);

        $response = $this->actingAs($this->proUser)->get(
            route('analysis.contextual.show', [
                'document' => $this->primaryDocument->id,
                'analysis' => $contextualAnalysis->id,
            ])
        );

        $response->assertOk();
        $response->assertViewIs('analysis-contextual');
        $response->assertSee('Auditoría Contextual PRO');
        $response->assertSee('Garantía de Fiel Cumplimiento');
        $response->assertSee('El anexo técnico rige en especificaciones operativas');
    }
}
