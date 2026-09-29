<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AnalysisType;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use App\Services\Dashboard\DashboardMetricsService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_organization_calculates_metrics_correctly(): void
    {
        $this->seed(PlanSeeder::class);

        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'is_active' => true]);
        $user = User::factory()->create(['organization_id' => $org->id]);

        $doc1 = Document::create([
            'organization_id' => $org->id,
            'uploaded_by' => $user->id,
            'original_filename' => 'doc1.pdf',
            'stored_filename' => 'doc1.pdf',
            'storage_path' => 'documents',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_hash' => md5('doc1'),
            'document_type' => DocumentType::Contract,
            'status' => DocumentStatus::Processed,
            'file_size_bytes' => 1024,
        ]);

        $doc2 = Document::create([
            'organization_id' => $org->id,
            'uploaded_by' => $user->id,
            'original_filename' => 'doc2.pdf',
            'stored_filename' => 'doc2.pdf',
            'storage_path' => 'documents',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_hash' => md5('doc2'),
            'document_type' => DocumentType::Financial,
            'status' => DocumentStatus::Processed,
            'file_size_bytes' => 2048,
        ]);

        $docPending = Document::create([
            'organization_id' => $org->id,
            'uploaded_by' => $user->id,
            'original_filename' => 'doc_pending.pdf',
            'stored_filename' => 'doc_pending.pdf',
            'storage_path' => 'documents',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_hash' => md5('doc_pending'),
            'document_type' => DocumentType::Other,
            'status' => DocumentStatus::Pending,
            'file_size_bytes' => 512,
        ]);

        // Older analysis for doc1 (should be ignored for latestAnalysis)
        $a1Old = DocumentAnalysis::create([
            'document_id' => $doc1->id,
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => DocumentStatus::Processed,
            'analysis_type' => AnalysisType::RiskAssessment,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['riesgos' => ['Risk A']],
            'processing_time_ms' => 1000,
            'input_tokens' => 100,
            'output_tokens' => 50,
            'version' => 1,
        ]);
        $a1Old->created_at = now()->subHours(2);
        $a1Old->save();

        // Newer analysis for doc1 (latest analysis)
        $a1New = DocumentAnalysis::create([
            'document_id' => $doc1->id,
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => DocumentStatus::Processed,
            'analysis_type' => AnalysisType::RiskAssessment,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['riesgos_identificados' => ['Risk 1', 'Risk 2']],
            'processing_time_ms' => 2000,
            'input_tokens' => 200,
            'output_tokens' => 100,
            'version' => 2,
        ]);
        $a1New->created_at = now()->subHour();
        $a1New->save();

        // Latest analysis for doc2
        $a2 = DocumentAnalysis::create([
            'document_id' => $doc2->id,
            'organization_id' => $org->id,
            'requested_by' => $user->id,
            'status' => DocumentStatus::Processed,
            'analysis_type' => AnalysisType::FullSummary,
            'llm_provider' => 'groq',
            'llm_model' => 'llama-3.3-70b-versatile',
            'content' => ['riesgos' => ['Risk X']],
            'processing_time_ms' => 4000,
            'input_tokens' => 300,
            'output_tokens' => 150,
            'version' => 1,
        ]);
        $a2->created_at = now()->subMinutes(30);
        $a2->save();

        $service = new DashboardMetricsService;
        $metrics = $service->forOrganization($user);

        $this->assertCount(3, $metrics['documents']);
        $this->assertEquals(2, $metrics['analyzedCount']);
        $this->assertEquals(3, $metrics['riskCount']); // 2 from doc1 latest + 1 from doc2 latest
        $this->assertEquals('3.0', $metrics['avgTime']); // (2000ms + 4000ms) / 2 = 3000ms = 3.0s
        $this->assertEquals(3584, $metrics['storageUsedBytes']);
        $this->assertEquals([$docPending->id], $metrics['pendingDocumentIds']);
    }

    public function test_query_count_and_performance_benchmark(): void
    {
        $this->seed(PlanSeeder::class);

        $org = Organization::create(['name' => 'Bench Org', 'slug' => 'bench-org', 'is_active' => true]);
        $user = User::factory()->create(['organization_id' => $org->id]);

        $documentsCount = 50;

        for ($i = 0; $i < $documentsCount; $i++) {
            $doc = Document::create([
                'organization_id' => $org->id,
                'uploaded_by' => $user->id,
                'original_filename' => "doc_{$i}.pdf",
                'stored_filename' => "doc_{$i}.pdf",
                'storage_path' => 'documents',
                'disk' => 'local',
                'mime_type' => 'application/pdf',
                'file_hash' => md5("doc_{$i}"),
                'document_type' => DocumentType::Contract,
                'status' => DocumentStatus::Processed,
                'file_size_bytes' => 1000,
            ]);

            // Older analysis
            $oldAnalysis = DocumentAnalysis::create([
                'document_id' => $doc->id,
                'organization_id' => $org->id,
                'requested_by' => $user->id,
                'status' => DocumentStatus::Processed,
                'analysis_type' => AnalysisType::RiskAssessment,
                'llm_provider' => 'groq',
                'llm_model' => 'llama-3.3-70b-versatile',
                'content' => ['riesgos' => ['Old Risk']],
                'processing_time_ms' => 1000,
                'version' => 1,
            ]);
            $oldAnalysis->created_at = now()->subMinutes(10);
            $oldAnalysis->save();

            // Latest analysis
            $newAnalysis = DocumentAnalysis::create([
                'document_id' => $doc->id,
                'organization_id' => $org->id,
                'requested_by' => $user->id,
                'status' => DocumentStatus::Processed,
                'analysis_type' => AnalysisType::RiskAssessment,
                'llm_provider' => 'groq',
                'llm_model' => 'llama-3.3-70b-versatile',
                'content' => ['riesgos_identificados' => ['Risk 1', 'Risk 2']],
                'processing_time_ms' => 2000,
                'version' => 2,
            ]);
            $newAnalysis->created_at = now()->subMinutes(5);
            $newAnalysis->save();
        }

        $service = new DashboardMetricsService;

        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = microtime(true);
        $metrics = $service->forOrganization($user);
        $endTime = microtime(true);

        $queryCount = count(DB::getQueryLog());
        $executionTimeMs = ($endTime - $startTime) * 1000;

        fwrite(STDERR, sprintf("\n[BENCHMARK RESULT - AFTER OPTIMIZATION]\nDocuments: %d\nQuery Count: %d\nExecution Time: %.2f ms\n", $documentsCount, $queryCount, $executionTimeMs));

        $this->assertCount($documentsCount, $metrics['documents']);
        $this->assertEquals($documentsCount * 2, $metrics['riskCount']);
    }
}
