<?php

declare(strict_types=1);

namespace Tests\Unit\Services\System;

use App\Services\System\ExternalServiceHealthService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalServiceHealthServiceTest extends TestCase
{
    private ExternalServiceHealthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExternalServiceHealthService;
    }

    public function test_groq_returns_unconfigured_when_api_key_is_missing(): void
    {
        config(['llm.providers.groq.api_key' => '']);

        $result = $this->service->checkGroq();

        $this->assertFalse($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('GROQ_API_KEY no está configurada.', $result['message']);
    }

    public function test_groq_returns_disconnected_on_http_failure(): void
    {
        config([
            'llm.providers.groq.api_key' => 'fake-key',
            'llm.providers.groq.base_url' => 'https://api.groq.com/openai/v1',
            'llm.providers.groq.model' => 'llama-3.3-70b-versatile',
        ]);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([], 500),
        ]);

        $result = $this->service->checkGroq();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('Groq respondió con estado HTTP 500.', $result['message']);
        $this->assertEquals([
            'model' => 'llama-3.3-70b-versatile',
            'status' => 500,
        ], $result['details']);
    }

    public function test_groq_returns_connected_when_model_is_available(): void
    {
        config([
            'llm.providers.groq.api_key' => 'fake-key',
            'llm.providers.groq.base_url' => 'https://api.groq.com/openai/v1',
            'llm.providers.groq.model' => 'llama-3.3-70b-versatile',
        ]);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([
                'data' => [
                    ['id' => 'llama-3.3-70b-versatile'],
                    ['id' => 'mixtral-8x7b-32768'],
                ],
            ], 200),
        ]);

        $result = $this->service->checkGroq();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertEquals('Groq conectado. Modelo [llama-3.3-70b-versatile] disponible.', $result['message']);
        $this->assertEquals([
            'model' => 'llama-3.3-70b-versatile',
            'model_available' => true,
            'models_count' => 2,
        ], $result['details']);
    }

    public function test_groq_returns_connected_warning_when_model_not_in_list(): void
    {
        config([
            'llm.providers.groq.api_key' => 'fake-key',
            'llm.providers.groq.base_url' => 'https://api.groq.com/openai/v1',
            'llm.providers.groq.model' => 'llama-3.3-70b-versatile',
        ]);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response([
                'data' => [
                    ['id' => 'other-model'],
                ],
            ], 200),
        ]);

        $result = $this->service->checkGroq();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertEquals('Groq conectado, pero el modelo [llama-3.3-70b-versatile] no aparece en la lista.', $result['message']);
        $this->assertEquals([
            'model' => 'llama-3.3-70b-versatile',
            'model_available' => false,
            'models_count' => 1,
        ], $result['details']);
    }

    public function test_groq_handles_exception_during_request(): void
    {
        config([
            'llm.providers.groq.api_key' => 'fake-key',
            'llm.providers.groq.base_url' => 'https://api.groq.com/openai/v1',
            'llm.providers.groq.model' => 'llama-3.3-70b-versatile',
        ]);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => fn () => throw new \Exception('Connection connection refused'),
        ]);

        $result = $this->service->checkGroq();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertStringContainsString('No se pudo conectar con Groq: Connection connection refused', $result['message']);
        $this->assertEquals(['model' => 'llama-3.3-70b-versatile'], $result['details']);
    }

    public function test_nvidia_nim_returns_unconfigured_when_api_key_is_missing(): void
    {
        config(['llm.providers.nvidia_nim.api_key' => '']);

        $result = $this->service->checkNvidiaNim();

        $this->assertFalse($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('NVIDIA_NIM_API_KEY no está configurada.', $result['message']);
    }

    public function test_nvidia_nim_returns_disconnected_on_http_failure(): void
    {
        config([
            'llm.providers.nvidia_nim.api_key' => 'fake-key',
            'llm.providers.nvidia_nim.base_url' => 'https://integrate.api.nvidia.com/v1',
            'llm.providers.nvidia_nim.model' => 'meta/llama-3.3-70b-instruct',
            'llm.providers.nvidia_nim.rate_limit_rpm' => 40,
        ]);

        Http::fake([
            'https://integrate.api.nvidia.com/v1/models' => Http::response([], 401),
        ]);

        $result = $this->service->checkNvidiaNim();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('NVIDIA NIM respondió con estado HTTP 401.', $result['message']);
        $this->assertEquals([
            'model' => 'meta/llama-3.3-70b-instruct',
            'status' => 401,
            'rate_limit_rpm' => 40,
        ], $result['details']);
    }

    public function test_nvidia_nim_returns_connected_when_model_is_available(): void
    {
        config([
            'llm.providers.nvidia_nim.api_key' => 'fake-key',
            'llm.providers.nvidia_nim.base_url' => 'https://integrate.api.nvidia.com/v1',
            'llm.providers.nvidia_nim.model' => 'meta/llama-3.3-70b-instruct',
            'llm.providers.nvidia_nim.rate_limit_rpm' => 40,
        ]);

        Http::fake([
            'https://integrate.api.nvidia.com/v1/models' => Http::response([
                'data' => [
                    ['id' => 'meta/llama-3.3-70b-instruct'],
                ],
            ], 200),
        ]);

        $result = $this->service->checkNvidiaNim();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertEquals('NVIDIA NIM conectado. Modelo [meta/llama-3.3-70b-instruct] disponible (Límite: 40 RPM).', $result['message']);
        $this->assertEquals([
            'model' => 'meta/llama-3.3-70b-instruct',
            'model_available' => true,
            'models_count' => 1,
            'rate_limit_rpm' => 40,
        ], $result['details']);
    }

    public function test_nvidia_nim_returns_connected_warning_when_model_not_in_list(): void
    {
        config([
            'llm.providers.nvidia_nim.api_key' => 'fake-key',
            'llm.providers.nvidia_nim.base_url' => 'https://integrate.api.nvidia.com/v1',
            'llm.providers.nvidia_nim.model' => 'meta/llama-3.3-70b-instruct',
            'llm.providers.nvidia_nim.rate_limit_rpm' => 40,
        ]);

        Http::fake([
            'https://integrate.api.nvidia.com/v1/models' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $result = $this->service->checkNvidiaNim();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertEquals('NVIDIA NIM conectado, pero el modelo [meta/llama-3.3-70b-instruct] no aparece en la lista.', $result['message']);
        $this->assertEquals([
            'model' => 'meta/llama-3.3-70b-instruct',
            'model_available' => false,
            'models_count' => 0,
            'rate_limit_rpm' => 40,
        ], $result['details']);
    }

    public function test_nvidia_nim_handles_exception_during_request(): void
    {
        config([
            'llm.providers.nvidia_nim.api_key' => 'fake-key',
            'llm.providers.nvidia_nim.base_url' => 'https://integrate.api.nvidia.com/v1',
            'llm.providers.nvidia_nim.model' => 'meta/llama-3.3-70b-instruct',
            'llm.providers.nvidia_nim.rate_limit_rpm' => 40,
        ]);

        Http::fake([
            'https://integrate.api.nvidia.com/v1/models' => fn () => throw new \Exception('Network timeout'),
        ]);

        $result = $this->service->checkNvidiaNim();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertStringContainsString('No se pudo conectar con NVIDIA NIM: Network timeout', $result['message']);
        $this->assertEquals([
            'model' => 'meta/llama-3.3-70b-instruct',
            'rate_limit_rpm' => 40,
        ], $result['details']);
    }

    public function test_unstructured_returns_unconfigured_when_url_is_missing(): void
    {
        config([
            'documents.unstructured.api_url' => '',
            'documents.unstructured.api_key' => 'fake-key',
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertFalse($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('UNSTRUCTURED_API_URL no está configurada.', $result['message']);
    }

    public function test_unstructured_returns_unconfigured_when_key_is_missing(): void
    {
        config([
            'documents.unstructured.api_url' => 'https://api.unstructured.io',
            'documents.unstructured.api_key' => '',
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertFalse($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('UNSTRUCTURED_API_KEY no está configurada.', $result['message']);
        $this->assertEquals(['api_url' => 'https://api.unstructured.io'], $result['details']);
    }

    public function test_unstructured_returns_disconnected_on_401_or_403(): void
    {
        config([
            'documents.unstructured.api_url' => 'https://api.unstructured.io',
            'documents.unstructured.api_key' => 'invalid-key',
        ]);

        Http::fake([
            'https://api.unstructured.io/healthcheck' => Http::response([], 401),
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('Unstructured rechazó la API key (HTTP 401).', $result['message']);
        $this->assertEquals(['api_url' => 'https://api.unstructured.io'], $result['details']);
    }

    public function test_unstructured_returns_disconnected_on_500_server_error(): void
    {
        config([
            'documents.unstructured.api_url' => 'https://api.unstructured.io',
            'documents.unstructured.api_key' => 'valid-key',
        ]);

        Http::fake([
            'https://api.unstructured.io/healthcheck' => Http::response([], 502),
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertEquals('Unstructured respondió con error del servidor (HTTP 502).', $result['message']);
        $this->assertEquals(['api_url' => 'https://api.unstructured.io'], $result['details']);
    }

    public function test_unstructured_returns_connected_on_successful_probe(): void
    {
        config([
            'documents.unstructured.api_url' => 'https://api.unstructured.io:8080/v1/parse',
            'documents.unstructured.api_key' => 'valid-key',
        ]);

        Http::fake([
            'https://api.unstructured.io:8080/healthcheck' => Http::response(['status' => 'ok'], 200),
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertEquals('Unstructured configurado y accesible.', $result['message']);
        $this->assertEquals([
            'api_url' => 'https://api.unstructured.io:8080/v1/parse',
            'probe_status' => 200,
        ], $result['details']);
    }

    public function test_unstructured_handles_legacy_general_url(): void
    {
        config([
            'documents.unstructured.api_url' => 'https://api.unstructured.io/general/v0/general',
            'documents.unstructured.api_key' => 'valid-key',
        ]);

        Http::fake([
            'https://api.unstructured.io' => Http::response([], 200),
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertTrue($result['configured']);
        $this->assertTrue($result['connected']);
        $this->assertEquals('Unstructured configurado y accesible.', $result['message']);
        $this->assertEquals([
            'api_url' => 'https://api.unstructured.io/general/v0/general',
            'probe_status' => 200,
        ], $result['details']);
    }

    public function test_unstructured_handles_exception_during_request(): void
    {
        config([
            'documents.unstructured.api_url' => 'https://api.unstructured.io',
            'documents.unstructured.api_key' => 'valid-key',
        ]);

        Http::fake([
            'https://api.unstructured.io/healthcheck' => fn () => throw new \Exception('Host unreachable'),
        ]);

        $result = $this->service->checkUnstructured();

        $this->assertTrue($result['configured']);
        $this->assertFalse($result['connected']);
        $this->assertStringContainsString('No se pudo conectar con Unstructured: Host unreachable', $result['message']);
        $this->assertEquals(['api_url' => 'https://api.unstructured.io'], $result['details']);
    }

    public function test_check_all_aggregates_all_service_checks(): void
    {
        config([
            'llm.providers.groq.api_key' => 'groq-key',
            'llm.providers.nvidia_nim.api_key' => 'nvidia-key',
            'documents.unstructured.api_url' => 'https://api.unstructured.io',
            'documents.unstructured.api_key' => 'unstructured-key',
        ]);

        Http::fake([
            'https://api.groq.com/openai/v1/models' => Http::response(['data' => [['id' => 'llama-3.3-70b-versatile']]], 200),
            'https://integrate.api.nvidia.com/v1/models' => Http::response(['data' => [['id' => 'meta/llama-3.3-70b-instruct']]], 200),
            'https://api.unstructured.io/healthcheck' => Http::response([], 200),
        ]);

        $result = $this->service->checkAll();

        $this->assertArrayHasKey('groq', $result);
        $this->assertArrayHasKey('nvidia_nim', $result);
        $this->assertArrayHasKey('unstructured', $result);

        $this->assertTrue($result['groq']['connected']);
        $this->assertTrue($result['nvidia_nim']['connected']);
        $this->assertTrue($result['unstructured']['connected']);
    }
}
