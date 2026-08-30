<?php

declare(strict_types=1);

namespace App\Services\LLM\Providers;

/**
 * Contract for all LLM provider adapters.
 *
 * Each adapter is responsible only for the HTTP transport layer:
 * building headers, the request payload, handling errors, and
 * extracting the raw response text.
 *
 * Prompts are always injected by the caller (LlmAnalysisService)
 * via the LlmPromptBuilder — adapters never build prompts themselves.
 */
interface LlmProviderAdapterInterface
{
    /**
     * Provider slug this adapter handles (matches config/llm.php key).
     *
     * Example: 'groq', 'cerebras', 'qwen'
     */
    public function providerSlug(): string;

    /**
     * Execute a chat completion request against the provider API.
     *
     * @param  string  $systemPrompt  Pre-built system prompt from LlmPromptBuilder
     * @param  string  $userPrompt    Pre-built user prompt from LlmPromptBuilder
     * @param  LlmProviderConfig  $config  Resolved configuration for this request
     * @param  string|null  $documentId  For contextual logging only
     * @return array{
     *     raw_content: string,
     *     input_tokens: int,
     *     output_tokens: int,
     *     processing_time_ms: int,
     * }
     *
     * @throws \RuntimeException On API failure, rate limiting, or token overflow
     */
    public function complete(
        string $systemPrompt,
        string $userPrompt,
        LlmProviderConfig $config,
        ?string $documentId = null,
    ): array;
}
