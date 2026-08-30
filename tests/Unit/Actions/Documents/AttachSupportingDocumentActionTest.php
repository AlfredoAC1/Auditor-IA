<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Documents;

use App\Actions\Documents\AttachSupportingDocumentAction;
use App\DTOs\Documents\AttachSupportingDocumentDTO;
use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Models\AnalysisSupportingDocument;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AttachSupportingDocumentActionTest extends TestCase
{
    use RefreshDatabase;

    private AttachSupportingDocumentAction $action;

    private Organization $organization;

    private User $user;

    private Document $primaryDocument;

    private DocumentAnalysis $analysis;

    private Document $supportingDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new AttachSupportingDocumentAction;

        $this->organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->primaryDocument = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'primary.pdf',
            'stored_filename' => 'primary.pdf',
            'storage_path' => 'documents/1',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => 'hash1',
            'status' => DocumentStatus::Processed,
        ]);

        $this->analysis = DocumentAnalysis::create([
            'document_id' => $this->primaryDocument->id,
            'organization_id' => $this->organization->id,
            'requested_by' => $this->user->id,
            'analysis_type' => AnalysisType::FullSummary,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['resumen' => 'Prueba'],
            'status' => DocumentStatus::Processed,
            'version' => 1,
        ]);

        $this->supportingDocument = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'supporting.pdf',
            'stored_filename' => 'supporting.pdf',
            'storage_path' => 'documents/1',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 2048,
            'file_hash' => 'hash2',
            'status' => DocumentStatus::Processed,
        ]);
    }

    public function test_attaches_supporting_document_successfully(): void
    {
        Log::spy();

        $dto = new AttachSupportingDocumentDTO(
            analysisId: $this->analysis->id,
            documentId: $this->supportingDocument->id,
            organizationId: $this->organization->id,
            attachedBy: $this->user->id,
            relationshipType: 'referenced',
            notes: 'Anexo de prueba',
        );

        $result = $this->action->execute($dto, $this->analysis, $this->supportingDocument);

        $this->assertInstanceOf(AnalysisSupportingDocument::class, $result);
        $this->assertEquals($this->analysis->id, $result->analysis_id);
        $this->assertEquals($this->supportingDocument->id, $result->document_id);
        $this->assertEquals($this->organization->id, $result->organization_id);
        $this->assertEquals($this->user->id, $result->attached_by);
        $this->assertEquals('referenced', $result->relationship_type);
        $this->assertEquals('Anexo de prueba', $result->notes);

        $this->assertDatabaseHas('analysis_supporting_documents', [
            'id' => $result->id,
            'analysis_id' => $this->analysis->id,
            'document_id' => $this->supportingDocument->id,
            'organization_id' => $this->organization->id,
            'attached_by' => $this->user->id,
            'relationship_type' => 'referenced',
            'notes' => 'Anexo de prueba',
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Supporting document attached to analysis.', [
                'analysis_id' => $this->analysis->id,
                'document_id' => $this->primaryDocument->id,
                'supporting_document_id' => $this->supportingDocument->id,
                'organization_id' => $this->organization->id,
                'attached_by' => $this->user->id,
                'relationship_type' => 'referenced',
            ]);
    }

    public function test_throws_exception_if_analysis_belongs_to_different_organization(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Other Org',
            'slug' => 'other-org',
            'is_active' => true,
        ]);

        $dto = new AttachSupportingDocumentDTO(
            analysisId: $this->analysis->id,
            documentId: $this->supportingDocument->id,
            organizationId: $otherOrg->id,
            attachedBy: $this->user->id,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Analysis [{$this->analysis->id}] does not belong to organization [{$otherOrg->id}].");

        $this->action->execute($dto, $this->analysis, $this->supportingDocument);
    }

    public function test_throws_exception_if_supporting_document_belongs_to_different_organization(): void
    {
        $otherOrg = Organization::create([
            'name' => 'Other Org',
            'slug' => 'other-org',
            'is_active' => true,
        ]);

        $otherSupportingDoc = Document::create([
            'organization_id' => $otherOrg->id,
            'uploaded_by' => User::factory()->create(['organization_id' => $otherOrg->id])->id,
            'original_filename' => 'other.pdf',
            'stored_filename' => 'other.pdf',
            'storage_path' => 'documents/2',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 512,
            'file_hash' => 'hash3',
            'status' => DocumentStatus::Processed,
        ]);

        $dto = new AttachSupportingDocumentDTO(
            analysisId: $this->analysis->id,
            documentId: $otherSupportingDoc->id,
            organizationId: $this->organization->id,
            attachedBy: $this->user->id,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Supporting document [{$otherSupportingDoc->id}] does not belong to organization [{$this->organization->id}].");

        $this->action->execute($dto, $this->analysis, $otherSupportingDoc);
    }

    public function test_throws_exception_if_supporting_document_is_primary_document(): void
    {
        $dto = new AttachSupportingDocumentDTO(
            analysisId: $this->analysis->id,
            documentId: $this->primaryDocument->id,
            organizationId: $this->organization->id,
            attachedBy: $this->user->id,
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Document [{$this->primaryDocument->id}] is the primary document of analysis [{$this->analysis->id}] and cannot be attached as a supporting document.");

        $this->action->execute($dto, $this->analysis, $this->primaryDocument);
    }

    public function test_throws_exception_if_document_is_already_attached(): void
    {
        $dto = new AttachSupportingDocumentDTO(
            analysisId: $this->analysis->id,
            documentId: $this->supportingDocument->id,
            organizationId: $this->organization->id,
            attachedBy: $this->user->id,
        );

        $this->action->execute($dto, $this->analysis, $this->supportingDocument);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Document [{$this->supportingDocument->id}] is already attached to analysis [{$this->analysis->id}].");

        $this->action->execute($dto, $this->analysis, $this->supportingDocument);
    }
}
