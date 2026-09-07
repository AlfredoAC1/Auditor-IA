<?php

declare(strict_types=1);

namespace App\Services\LLM\Providers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI-compatible adapter for providers that implement the
 * standard /chat/completions endpoint with Bearer token auth.
 *
 * Covers: Groq, Cerebras, DeepSeek, Qwen Cloud (via DashScope),
 * NVIDIA NIM, and any other OpenAI-compatible endpoint.
 *
 * Provider-specific quirks (e.g. rate limiting) are handled by
 * subclassing this adapter or passing rateLimitRpm via config.
 */
class OpenAiCompatibleAdapter implements LlmProviderAdapterInterface
{
    public function providerSlug(): string
    {
        return 'openai_compatible';
    }

    /**
     * @return array{raw_content: string, input_tokens: int, output_tokens: int, processing_time_ms: int}
     *
     * @throws \RuntimeException
     */
    public function complete(
        string $systemPrompt,
        string $userPrompt,
        LlmProviderConfig $config,
        ?string $documentId = null,
    ): array {
        $endpoint = rtrim($config->baseUrl, '/').'/chat/completions';

        $headers = array_merge([
            'Authorization' => "Bearer {$config->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $config->extraHeaders);

        $payload = $this->buildPayload($config, $systemPrompt, $userPrompt);

        Log::info("LLM [{$config->provider}] request started.", [
            'document_id' => $documentId,
            'model' => $config->model,
            'content_length' => mb_strlen($userPrompt),
        ]);

        $startTime = microtime(true);

        $response = Http::timeout($config->timeoutSeconds)
            ->withHeaders($headers)
            ->post($endpoint, $payload);

        $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        if ($response->failed()) {
            $this->handleError($response, $config, $documentId);
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;

        if ($content === null) {
            throw new \RuntimeException(
                "[{$config->provider}] API response does not contain expected message content."
            );
        }

        $usage = $data['usage'] ?? [];

        Log::info("LLM [{$config->provider}] request completed.", [
            'document_id' => $documentId,
            'model' => $config->model,
            'input_tokens' => $usage['prompt_tokens'] ?? 0,
            'output_tokens' => $usage['completion_tokens'] ?? 0,
            'processing_time_ms' => $processingTimeMs,
        ]);

        return [
            'raw_content' => $content,
            'input_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
            'output_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'processing_time_ms' => $processingTimeMs,
        ];
    }

    /**
     * Build the request payload.
     *
     * Subclasses may override to add provider-specific params (e.g. top_p, stream).
     *
     * @return array<string, mixed>
     */
    protected function buildPayload(
        LlmProviderConfig $config,
        string $systemPrompt,
        string $userPrompt,
    ): array {
        return [
            'model' => $config->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'max_tokens' => $config->maxTokens,
            'temperature' => $config->temperature,
            'response_format' => ['type' => 'json_object'],
        ];
    }

    /**
     * Translate HTTP errors into descriptive RuntimeExceptions.
     *
     * @throws \RuntimeException always
     */
    protected function handleError(
        Response $response,
        LlmProviderConfig $config,
        ?string $documentId,
    ): never {
        $status = $response->status();
        $errorBody = $response->body();

        Log::error("LLM [{$config->provider}] request failed.", [
            'document_id' => $documentId,
            'model' => $config->model,
            'status' => $status,
            'error' => mb_substr($errorBody, 0, 500),
        ]);

        // 429 Rate Limit
        if ($status === 429 || str_contains($errorBody, 'rate limit') || str_contains($errorBody, 'Too Many Requests')) {
            $retryAfter = $response->header('Retry-After');
            $waitMsg = $retryAfter !== '' ? " Retry after {$retryAfter} seconds." : '';
            throw new \RuntimeException(
                "[{$config->provider}] Rate limit reached.{$waitMsg} Status [429]."
            );
        }

        // Token overflow
        if (str_contains($errorBody, 'max completion tokens reached') || str_contains($errorBody, 'json_validate_failed')) {
            throw new \RuntimeException(
                "[{$config->provider}] Token limit reached for model [{$config->model}] (max_tokens={$config->maxTokens}). ".
                'Try increasing the token limit in your .env configuration.'
            );
        }

        $decoded = json_decode($errorBody, true);
        $errorMessage = $decoded['detail']
            ?? $decoded['error']['message']
            ?? $errorBody;

        throw new \RuntimeException(
            "[{$config->provider}] API returned status [{$status}]: ".mb_substr((string) $errorMessage, 0, 200)
        );
    }
}
