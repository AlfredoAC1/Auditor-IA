<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use App\Support\Demo\StaticDemoDataset;
use Illuminate\Database\Seeder;

/**
 * Seeds sample audited legal documents into the Demo Pro organization
 * using the canonical StaticDemoDataset payloads.
 *
 * Intended for local development or future Supabase PostgreSQL provisioning.
 * Never executed in the static Vercel demo build.
 */
class DemoSampleDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $proOrg = Organization::query()->where('slug', 'demo-pro')->first();
        if ($proOrg === null) {
            $this->call(DemoUsersSeeder::class);
            $proOrg = Organization::query()->where('slug', 'demo-pro')->firstOrFail();
        }

        $proUser = User::query()
            ->where('organization_id', $proOrg->id)
            ->firstOrFail();

        foreach (StaticDemoDataset::documents() as $index => $item) {
            $docType = match ($item['slug']) {
                'contrato-arriendo-comercial' => DocumentType::LeaseAgreement,
                default => DocumentType::Contract,
            };

            $filename = (string) $item['original_filename'];
            $hash = hash('sha256', 'demo-sample:'.$item['slug']);

            $document = Document::updateOrCreate(
                [
                    'organization_id' => $proOrg->id,
                    'file_hash' => $hash,
                ],
                [
                    'uploaded_by' => $proUser->id,
                    'original_filename' => $filename,
                    'stored_filename' => $item['slug'].'.pdf',
                    'storage_path' => 'demo-samples/'.$item['slug'],
                    'disk' => config('documents.storage_disk', 'local'),
                    'mime_type' => (string) $item['mime_type'],
                    'file_size_bytes' => (int) $item['file_size_bytes'],
                    'status' => DocumentStatus::Processed,
                    'document_type' => $docType,
                    'metadata' => [
                        'demo_slug' => $item['slug'],
                        'seeded' => true,
                    ],
                    'processed_at' => now()->subDays(max(1, 7 - $index * 2)),
                ],
            );

            $analysisData = $item['analysis'];

            DocumentAnalysis::updateOrCreate(
                [
                    'document_id' => $document->id,
                    'organization_id' => $proOrg->id,
                    'version' => 1,
                ],
                [
                    'requested_by' => $proUser->id,
                    'analysis_type' => $analysisData['analysis_type'],
                    'llm_provider' => $analysisData['llm_provider'],
                    'llm_model' => $analysisData['llm_model'],
                    'content' => $analysisData['content'],
                    'confidence_score' => 0.94,
                    'input_tokens' => (int) $analysisData['input_tokens'],
                    'output_tokens' => (int) $analysisData['output_tokens'],
                    'processing_time_ms' => (int) $analysisData['processing_time_ms'],
                    'prompt_metadata' => ['source' => 'DemoSampleDocumentsSeeder'],
                    'status' => DocumentStatus::Processed,
                ],
            );
        }

        $this->command?->info('Demo sample documents seeded for organization: '.$proOrg->slug);
    }
}
