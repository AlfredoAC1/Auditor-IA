<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies connectivity and configuration for external API services.
 */
final class ExternalServiceHealthService
{
    /**
     * @return array<string, array{configured: bool, connected: bool, message: string, details?: array<string, mixed>}>
     */
    public function checkAll(): array
    {
        return [
            'groq' => $this->checkGroq(),
            'nvidia_nim' => $this->checkNvidiaNim(),
            'unstructured' => $this->checkUnstructured(),
        ];
    }

    /**
     * @return array{configured: bool, connected: bool, message: string, details?: array<string, mixed>}
     */
    public function checkGroq(): array
    {
        $config = (array) config('llm.providers.groq', []);
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api.groq.com/openai/v1'), '/');
        $model = (string) ($config['model'] ?? 'llama-3.3-70b-versatile');

        if ($apiKey === '') {
            return [
                'configured' => false,
                'connected' => false,
                'message' => 'GROQ_API_KEY no está configurada.',
            ];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                ])
                ->get("{$baseUrl}/models");

            if ($response->failed()) {
                return [
                    'configured' => true,
                    'connected' => false,
                    'message' => "Groq respondió con estado HTTP {$response->status()}.",
                    'details' => [
                        'model' => $model,
                        'status' => $response->status(),
                    ],
                ];
            }

            $models = collect($response->json('data', []))
                ->pluck('id')
                ->filter()
                ->values()
                ->all();

            $modelAvailable = in_array($model, $models, true);

            return [
                'configured' => true,
                'connected' => true,
                'message' => $modelAvailable
                    ? "Groq conectado. Modelo [{$model}] disponible."
                    : "Groq conectado, pero el modelo [{$model}] no aparece en la lista.",
                'details' => [
                    'model' => $model,
                    'model_available' => $modelAvailable,
                    'models_count' => count($models),
                ],
            ];
        } catch (\Throwable $exception) {
            Log::warning('Groq connectivity check failed.', [
                'error' => $exception->getMessage(),
            ]);

            return [
                'configured' => true,
                'connected' => false,
                'message' => 'No se pudo conectar con Groq: ' . $exception->getMessage(),
                'details' => ['model' => $model],
            ];
        }
    }

    /**
     * @return array{configured: bool, connected: bool, message: string, details?: array<string, mixed>}
     */
    public function checkNvidiaNim(): array
    {
        $config = (array) config('llm.providers.nvidia_nim', []);
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://integrate.api.nvidia.com/v1'), '/');
        $model = (string) ($config['model'] ?? 'meta/llama-3.3-70b-instruct');
        $rpm = (int) ($config['rate_limit_rpm'] ?? 40);

        if ($apiKey === '') {
            return [
                'configured' => false,
                'connected' => false,
                'message' => 'NVIDIA_NIM_API_KEY no está configurada.',
            ];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])
                ->get("{$baseUrl}/models");

            if ($response->failed()) {
                return [
                    'configured' => true,
                    'connected' => false,
                    'message' => "NVIDIA NIM respondió con estado HTTP {$response->status()}.",
                    'details' => [
                        'model' => $model,
                        'status' => $response->status(),
                        'rate_limit_rpm' => $rpm,
                    ],
                ];
            }

            $models = collect($response->json('data', []))
                ->pluck('id')
                ->filter()
                ->values()
                ->all();

            $modelAvailable = in_array($model, $models, true);

            return [
                'configured' => true,
                'connected' => true,
                'message' => $modelAvailable
                    ? "NVIDIA NIM conectado. Modelo [{$model}] disponible (Límite: {$rpm} RPM)."
                    : "NVIDIA NIM conectado, pero el modelo [{$model}] no aparece en la lista.",
                'details' => [
                    'model' => $model,
                    'model_available' => $modelAvailable,
                    'models_count' => count($models),
                    'rate_limit_rpm' => $rpm,
                ],
            ];
        } catch (\Throwable $exception) {
            Log::warning('NVIDIA NIM connectivity check failed.', [
                'error' => $exception->getMessage(),
            ]);

            return [
                'configured' => true,
                'connected' => false,
                'message' => 'No se pudo conectar con NVIDIA NIM: ' . $exception->getMessage(),
                'details' => [
                    'model' => $model,
                    'rate_limit_rpm' => $rpm,
                ],
            ];
        }
    }

    /**
     * @return array{configured: bool, connected: bool, message: string, details?: array<string, mixed>}
     */
    public function checkUnstructured(): array
    {
        $apiUrl = trim((string) config('documents.unstructured.api_url', ''));
        $apiKey = trim((string) config('documents.unstructured.api_key', ''));

        if ($apiUrl === '') {
            return [
                'configured' => false,
                'connected' => false,
                'message' => 'UNSTRUCTURED_API_URL no está configurada.',
            ];
        }

        if ($apiKey === '') {
            return [
                'configured' => false,
                'connected' => false,
                'message' => 'UNSTRUCTURED_API_KEY no está configurada.',
                'details' => ['api_url' => $apiUrl],
            ];
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'unstructured-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($this->resolveUnstructuredHealthUrl($apiUrl));

            if ($response->status() === 401 || $response->status() === 403) {
                return [
                    'configured' => true,
                    'connected' => false,
                    'message' => 'Unstructured rechazó la API key (HTTP ' . $response->status() . ').',
                    'details' => ['api_url' => $apiUrl],
                ];
            }

            // Any HTTP response means the host is reachable (404/405 are acceptable for probes).
            if ($response->status() >= 500) {
                return [
                    'configured' => true,
                    'connected' => false,
                    'message' => 'Unstructured respondió con error del servidor (HTTP ' . $response->status() . ').',
                    'details' => ['api_url' => $apiUrl],
                ];
            }

            return [
                'configured' => true,
                'connected' => true,
                'message' => 'Unstructured configurado y accesible.',
                'details' => [
                    'api_url' => $apiUrl,
                    'probe_status' => $response->status(),
                ],
            ];
        } catch (\Throwable $exception) {
            Log::warning('Unstructured connectivity check failed.', [
                'error' => $exception->getMessage(),
            ]);

            return [
                'configured' => true,
                'connected' => false,
                'message' => 'No se pudo conectar con Unstructured: ' . $exception->getMessage(),
                'details' => ['api_url' => $apiUrl],
            ];
        }
    }

    private function resolveUnstructuredHealthUrl(string $apiUrl): string
    {
        $parsed = parse_url($apiUrl);

        if (! is_array($parsed) || ! isset($parsed['scheme'], $parsed['host'])) {
            return $apiUrl;
        }

        $base = $parsed['scheme'] . '://' . $parsed['host'];

        if (isset($parsed['port'])) {
            $base .= ':' . $parsed['port'];
        }

        // Legacy partition API has no dedicated health endpoint; probe the service root.
        if (str_contains($apiUrl, '/general/v0/general')) {
            $path = $parsed['path'] ?? '';
            $basePath = str_replace('/general/v0/general', '', $path);

            return $base . ($basePath !== '' ? $basePath : '');
        }

        return $base . '/healthcheck';
    }
}
