<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\System\ExternalServiceHealthService;
use Illuminate\Console\Command;

class CheckExternalServicesCommand extends Command
{
    protected $signature = 'services:check';

    protected $description = 'Verifica la configuración y conectividad de Groq y Unstructured';

    public function handle(ExternalServiceHealthService $healthService): int
    {
        $this->info('Verificando servicios externos...');
        $this->newLine();

        $results = $healthService->checkAll();
        $allConnected = true;

        foreach ($results as $service => $result) {
            $label = strtoupper($service);
            $status = $result['connected'] ? '<fg=green>OK</>' : '<fg=red>FAIL</>';

            $this->line("  [{$status}] {$label}: {$result['message']}");

            if (! $result['connected']) {
                $allConnected = false;
            }
        }

        $this->newLine();

        if ($allConnected) {
            $this->info('Todos los servicios están conectados.');

            return self::SUCCESS;
        }

        $this->error('Uno o más servicios no están disponibles. Revisa tu archivo .env.');

        return self::FAILURE;
    }
}
