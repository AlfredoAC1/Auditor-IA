<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Documents;

use App\Services\Documents\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentStorageServiceTest extends TestCase
{
    private DocumentStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('s3');

        $this->service = new DocumentStorageService;
    }

    public function test_store_successfully_saves_file_and_returns_correct_metadata(): void
    {
        $organizationId = 42;
        $fileContent = 'Sample document content for testing storage service.';
        $file = UploadedFile::fake()->createWithContent('contract.pdf', $fileContent);

        $result = $this->service->store($file, $organizationId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('stored_filename', $result);
        $this->assertArrayHasKey('storage_path', $result);
        $this->assertArrayHasKey('disk', $result);
        $this->assertArrayHasKey('file_hash', $result);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertArrayHasKey('file_size_bytes', $result);

        $this->assertEquals('local', $result['disk']);
        $this->assertEquals(strlen($fileContent), $result['file_size_bytes']);
        $this->assertEquals(hash('sha256', $fileContent), $result['file_hash']);
        $this->assertStringEndsWith('.pdf', $result['stored_filename']);

        $expectedPath = sprintf('documents/%d/%s/%s', $organizationId, now()->format('Y'), now()->format('m'));
        $this->assertEquals($expectedPath, $result['storage_path']);

        Storage::disk('local')->assertExists($result['storage_path'].'/'.$result['stored_filename']);
    }

    public function test_store_uses_custom_disk_and_base_path_from_config(): void
    {
        Config::set('documents.storage.disk', 's3');
        Config::set('documents.storage.base_path', 'tenant-docs');

        $organizationId = 10;
        $file = UploadedFile::fake()->create('report.docx', 300, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $result = $this->service->store($file, $organizationId);

        $this->assertEquals('s3', $result['disk']);
        $this->assertStringStartsWith('tenant-docs/10/', $result['storage_path']);
        $this->assertStringEndsWith('.docx', $result['stored_filename']);

        Storage::disk('s3')->assertExists($result['storage_path'].'/'.$result['stored_filename']);
    }

    public function test_store_preserves_allowed_extension_case_insensitively(): void
    {
        $file = UploadedFile::fake()->create('DOCUMENT.PDF', 100, 'application/pdf');

        $result = $this->service->store($file, 1);

        $this->assertStringEndsWith('.pdf', $result['stored_filename']);
    }

    public function test_store_falls_back_to_guessed_extension_when_extension_is_disallowed(): void
    {
        $file = UploadedFile::fake()->create('script.php', 100, 'application/pdf');

        $result = $this->service->store($file, 1);

        // Client extension .php is not allowed, so guessExtension() guesses pdf from application/pdf
        $this->assertStringEndsWith('.pdf', $result['stored_filename']);
    }

    public function test_store_falls_back_to_bin_when_extension_disallowed_and_cannot_be_guessed(): void
    {
        $file = UploadedFile::fake()->create('unknown.xyz', 100, 'application/x-custom-unknown');

        $result = $this->service->store($file, 1);

        $this->assertStringEndsWith('.bin', $result['stored_filename']);
    }

    public function test_store_throws_exception_when_storage_put_fails(): void
    {
        $diskMock = \Mockery::mock();
        $diskMock->shouldReceive('putFileAs')->once()->andReturn(false);

        Storage::shouldReceive('disk')->with('local')->andReturn($diskMock);

        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to store document for organization [5].');

        $this->service->store($file, 5);
    }

    public function test_store_throws_exception_when_real_path_is_unavailable(): void
    {
        $diskMock = \Mockery::mock();
        $diskMock->shouldReceive('putFileAs')->once()->andReturn('documents/1/filename.pdf');
        Storage::shouldReceive('disk')->with('local')->andReturn($diskMock);

        $fileMock = \Mockery::mock(UploadedFile::class)->makePartial();
        $fileMock->shouldReceive('getClientOriginalExtension')->andReturn('pdf');
        $fileMock->shouldReceive('getMimeType')->andReturn('application/pdf');
        $fileMock->shouldReceive('getSize')->andReturn(100);
        $fileMock->shouldReceive('getRealPath')->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot compute hash: file path unavailable.');

        $this->service->store($fileMock, 1);
    }

    public function test_exists_returns_true_when_file_exists_and_false_otherwise(): void
    {
        Storage::disk('local')->put('documents/1/file.pdf', 'content');

        $this->assertTrue($this->service->exists('documents/1', 'file.pdf', 'local'));
        $this->assertFalse($this->service->exists('documents/1', 'nonexistent.pdf', 'local'));
    }

    public function test_delete_removes_file_from_disk(): void
    {
        Storage::disk('local')->put('documents/1/file.pdf', 'content');
        Storage::disk('local')->assertExists('documents/1/file.pdf');

        $result = $this->service->delete('documents/1', 'file.pdf', 'local');

        $this->assertTrue($result);
        Storage::disk('local')->assertMissing('documents/1/file.pdf');
    }
}
