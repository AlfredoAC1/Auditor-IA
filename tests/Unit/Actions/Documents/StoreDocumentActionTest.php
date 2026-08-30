<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Documents;

use App\Actions\Documents\StoreDocumentAction;
use App\DTOs\Documents\StoreDocumentDTO;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Events\Documents\DocumentUploaded;
use App\Jobs\Documents\ProcessDocumentJob;
use App\Models\Document;
use App\Models\Organization;
use App\Models\User;
use App\Services\Documents\DocumentProcessingDispatcher;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreDocumentActionTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;

    private User $user;

    private DocumentStorageService $storageService;

    private DocumentProcessingDispatcher $processingDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Bus::fake([ProcessDocumentJob::class]);

        $this->organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->storageService = new DocumentStorageService;
        $this->processingDispatcher = new DocumentProcessingDispatcher;
    }

    public function test_stores_document_successfully(): void
    {
        Event::fake([DocumentUploaded::class]);
        Log::spy();

        $uploadedFile = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $dto = new StoreDocumentDTO(
            file: $uploadedFile,
            organizationId: $this->organization->id,
            uploadedBy: $this->user->id,
            documentType: DocumentType::Contract,
            metadata: ['category' => 'legal'],
        );

        $action = new StoreDocumentAction($this->storageService, $this->processingDispatcher);

        $document = $action->execute($dto, '127.0.0.1', false);

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals($this->organization->id, $document->organization_id);
        $this->assertEquals($this->user->id, $document->uploaded_by);
        $this->assertEquals('contract.pdf', $document->original_filename);
        $this->assertEquals('local', $document->disk);
        $this->assertEquals('application/pdf', $document->mime_type);
        $this->assertEquals(DocumentStatus::Pending, $document->status);
        $this->assertEquals(DocumentType::Contract, $document->document_type);
        $this->assertEquals(['category' => 'legal'], $document->metadata);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'organization_id' => $this->organization->id,
            'stored_filename' => $document->stored_filename,
        ]);

        Storage::disk('local')->assertExists($document->storage_path.'/'.$document->stored_filename);

        Bus::assertDispatched(ProcessDocumentJob::class, function (ProcessDocumentJob $job) use ($document) {
            return $job->document->id === $document->id;
        });

        Event::assertDispatched(DocumentUploaded::class, function (DocumentUploaded $event) use ($document) {
            return $event->document->id === $document->id
                && $event->uploadedBy === $this->user->id
                && $event->ipAddress === '127.0.0.1';
        });

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Document uploaded successfully.', [
                'document_id' => $document->id,
                'organization_id' => $this->organization->id,
                'uploaded_by' => $this->user->id,
                'file_size_bytes' => $document->file_size_bytes,
            ]);
    }

    public function test_deletes_stored_file_and_logs_error_when_database_transaction_fails(): void
    {
        Log::spy();

        $uploadedFile = UploadedFile::fake()->create('failed.pdf', 200, 'application/pdf');

        $dto = new StoreDocumentDTO(
            file: $uploadedFile,
            organizationId: $this->organization->id,
            uploadedBy: $this->user->id,
            documentType: DocumentType::Contract,
        );

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function (): void {
                // Confirm the file was stored on disk before database insertion failed
                $files = Storage::disk('local')->allFiles();
                $this->assertCount(1, $files, 'File should be present in storage before DB failure.');

                throw new \RuntimeException('Database transaction error');
            });

        $action = new StoreDocumentAction($this->storageService, $this->processingDispatcher);

        $exceptionThrown = false;

        try {
            $action->execute($dto);
        } catch (\RuntimeException $exception) {
            $exceptionThrown = true;
            $this->assertEquals('Database transaction error', $exception->getMessage());
        }

        $this->assertTrue($exceptionThrown, 'Expected RuntimeException was not thrown.');

        // Verify file cleanup in storageService->delete() rollback block
        $filesAfterRollback = Storage::disk('local')->allFiles();
        $this->assertEmpty($filesAfterRollback, 'Stored file was not deleted from storage after database transaction failed.');

        Bus::assertNotDispatched(ProcessDocumentJob::class);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Document creation failed, file cleaned up.', [
                'organization_id' => $this->organization->id,
                'uploaded_by' => $this->user->id,
                'error' => 'Database transaction error',
            ]);
    }
}
