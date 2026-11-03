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

$user = User::first();

$service = new DashboardMetricsService();

$start = microtime(true);
$memoryStart = memory_get_usage();

for ($i = 0; $i < 10; $i++) {
    $service->forOrganization($user);
}

$end = microtime(true);
$memoryEnd = memory_get_usage();

echo "Time: " . (($end - $start) / 10) . " seconds\n";
echo "Memory: " . (($memoryEnd - $memoryStart) / 1024 / 1024) . " MB\n";
