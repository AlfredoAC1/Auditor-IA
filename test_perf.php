<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Models\Organization;
use App\Models\User;
use App\Services\Dashboard\DashboardMetricsService;
use App\Enums\DocumentStatus;
use Illuminate\Support\Facades\DB;
use App\Models\AiModel;

// Setup test data
DB::statement('PRAGMA foreign_keys = OFF');
User::truncate();
Organization::truncate();
Document::truncate();
DocumentAnalysis::truncate();
AiModel::truncate();
DB::statement('PRAGMA foreign_keys = ON');

$org = Organization::create(['name' => 'Test Org']);
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'organization_id' => $org->id,
]);

$docsToInsert = [];
$analysesToInsert = [];
$now = now()->toDateTimeString();

for ($i = 0; $i < 10000; $i++) {
    $docId = Illuminate\Support\Str::uuid()->toString();
    $docsToInsert[] = [
        'id' => $docId,
        'organization_id' => $org->id,
        'uploaded_by' => $user->id,
        'original_filename' => "doc_{$i}.pdf",
        'stored_filename' => "doc_{$i}.pdf",
        'storage_path' => "docs/doc_{$i}.pdf",
        'mime_type' => "application/pdf",
        'file_size_bytes' => 1024,
        'file_hash' => "hash{$i}",
        'status' => 'processed', // Value from DocumentStatus::Processed
        'created_at' => $now,
        'updated_at' => $now,
    ];

    $analysesToInsert[] = [
        'id' => Illuminate\Support\Str::uuid()->toString(),
        'document_id' => $docId,
        'organization_id' => $org->id,
        'status' => 'processed',
        'content' => json_encode(['riesgos' => ['risk 1', 'risk 2']]),
        'processing_time_ms' => 1500,
        'input_tokens' => 100,
        'output_tokens' => 50,
        'analysis_type' => 'risk_assessment',
        'llm_provider' => 'cerebras',
        'llm_model' => 'cerebras-gpt',
        'created_at' => $now,
        'updated_at' => $now,
    ];
}

foreach (array_chunk($docsToInsert, 1000) as $chunk) {
    Document::insert($chunk);
}

foreach (array_chunk($analysesToInsert, 1000) as $chunk) {
    DocumentAnalysis::insert($chunk);
}

$service = new DashboardMetricsService();

$start = microtime(true);
$memoryStart = memory_get_usage();

for ($i = 0; $i < 5; $i++) {
    $service->forOrganization($user);
}

$end = microtime(true);
$memoryEnd = memory_get_usage();

echo "Time: " . (($end - $start) / 5) . " seconds\n";
echo "Memory: " . (($memoryEnd - $memoryStart) / 1024 / 1024) . " MB\n";
