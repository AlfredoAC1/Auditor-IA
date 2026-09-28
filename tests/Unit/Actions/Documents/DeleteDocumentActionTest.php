<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Documents;

use App\Actions\Documents\DeleteDocumentAction;
use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\DocumentChunk;
use App\Models\Organization;
use App\Models\User;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteDocumentActionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $user;

    private Document $document;

    private DocumentStorageService $storageService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->storageService = new DocumentStorageService;
    }

    private function createDocumentWithFile(): Document
    {
        $uploadedFile = UploadedFile::fake()->create('test_document.pdf', 500, 'application/pdf');

        $storageInfo = $this->storageService->store($uploadedFile, $this->organization->id);

        $document = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'test_document.pdf',
            'stored_filename' => $storageInfo['stored_filename'],
            'storage_path' => $storageInfo['storage_path'],
            'disk' => $storageInfo['disk'],
            'mime_type' => $storageInfo['mime_type'],
            'file_size_bytes' => $storageInfo['file_size_bytes'],
            'file_hash' => $storageInfo['file_hash'],
            'status' => DocumentStatus::Processed,
        ]);

        DocumentAnalysis::create([
            'document_id' => $document->id,
            'organization_id' => $this->organization->id,
            'requested_by' => $this->user->id,
            'analysis_type' => AnalysisType::FullSummary,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['resumen' => 'Prueba'],
            'status' => DocumentStatus::Processed,
            'version' => 1,
        ]);

        DocumentChunk::create([
            'document_id' => $document->id,
            'organization_id' => $this->organization->id,
            'chunk_index' => 1,
            'content' => 'Chunk content',
            'token_count' => 10,
        ]);

        return $document;
    }

    public function test_deletes_document_and_associated_records_and_storage_file_successfully(): void
    {
        Log::spy();

        $document = $this->createDocumentWithFile();
        $documentId = $document->id;

        // Assert file exists before deletion
        Storage::disk($document->disk)->assertExists($document->storage_path.'/'.$document->stored_filename);

        $action = new DeleteDocumentAction($this->storageService);
        $action->execute($document);

        // Assert file deleted
        Storage::disk($document->disk)->assertMissing($document->storage_path.'/'.$document->stored_filename);

        // Assert DB records soft/hard deleted appropriately
        // Document uses SoftDeletes but the action uses forceDelete()
        $this->assertDatabaseMissing('documents', ['id' => $documentId]);
        $this->assertDatabaseMissing('document_analyses', ['document_id' => $documentId]);
        $this->assertDatabaseMissing('document_chunks', ['document_id' => $documentId]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Document deleted in cascade successfully.', \Mockery::on(function ($data) use ($documentId) {
                return $data['document_id'] === $documentId
                    && $data['organization_id'] === $this->organization->id
                    && $data['filename'] === 'test_document.pdf';
            }));
    }

    public function test_continues_db_deletion_even_if_file_deletion_fails(): void
    {
        Log::spy();

        $document = $this->createDocumentWithFile();
        $documentId = $document->id;

        // Force a storage failure by replacing the service with a failing anonymous class or mock if possible
        // Since DocumentStorageService is final, we can't easily mock it without affecting class loading unless we use a partial mock bypassing type hints.
        // Let's use \Mockery::mock on an overloaded Storage facade method to throw exception.
        Storage::shouldReceive('disk')->andThrow(new \RuntimeException('Storage failure'));

        $action = new DeleteDocumentAction($this->storageService);
        $action->execute($document);

        // Assert DB records deleted despite storage failure
        $this->assertDatabaseMissing('documents', ['id' => $documentId]);
        $this->assertDatabaseMissing('document_analyses', ['document_id' => $documentId]);
        $this->assertDatabaseMissing('document_chunks', ['document_id' => $documentId]);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('File cleanup failed during document deletion, continuing DB purge.', \Mockery::on(function ($data) use ($documentId) {
                return $data['document_id'] === $documentId
                    && $data['error'] === 'Storage failure';
            }));

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Document deleted in cascade successfully.', \Mockery::any());
    }

    public function test_skips_file_deletion_if_storage_path_or_stored_filename_is_empty(): void
    {
        Log::spy();

        // Ensure database accepts null by looking at the constraint error, stored_filename is NOT NULL.
        // Actually, Document's stored_filename might not be nullable in schema, let's just make it empty string if not nullable,
        // but let's check migration for `documents` table to see if it allows null.
        // The error was: Integrity constraint violation: 19 NOT NULL constraint failed: documents.stored_filename
        // So stored_filename is not nullable. However, DeleteDocumentAction code says: `if ($storagePath && $storedFilename)`
        // We can just use empty string `''` to simulate empty values which makes the condition evaluate to false.

        $documentWithoutFile = Document::create([
            'organization_id' => $this->organization->id,
            'uploaded_by' => $this->user->id,
            'original_filename' => 'no_file.pdf',
            'stored_filename' => '',
            'storage_path' => '',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 0,
            'file_hash' => 'dummy',
            'status' => DocumentStatus::Pending,
        ]);
        $documentId = $documentWithoutFile->id;

        // Ensure Storage facade is not called for deletion
        Storage::shouldReceive('disk')->never();

        $action = new DeleteDocumentAction($this->storageService);
        $action->execute($documentWithoutFile);

        $this->assertDatabaseMissing('documents', ['id' => $documentId]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Document deleted in cascade successfully.', \Mockery::any());
    }
}
