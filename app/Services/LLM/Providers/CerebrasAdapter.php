<?php

declare(strict_types=1);

namespace App\Services\LLM\Providers;

/**
 * Adapter for the Cerebras Inference API.
 *
 * The Cerebras API is OpenAI-compatible but differs in one important way:
 * it expects `max_completion_tokens` instead of `max_tokens` in the request
 * payload. Sending `max_tokens` is silently ignored, meaning responses would
 * always run to the model's own maximum — unpredictable and expensive.
 *
 * This adapter extends the generic OpenAI-compatible transport and overrides
 * only the payload-building step to correct this parameter name.
 *
 * Supported models (as of 2026):
 *   - gemma-4-31b  (Preview) — up to 40,960 completion tokens
 *
 * Docs: https://inference-docs.cerebras.ai/api-reference/chat-completions
 */
final class CerebrasAdapter extends OpenAiCompatibleAdapter
{
    public function providerSlug(): string
    {
        return 'cerebras';
    }

    /**
     * Build the Cerebras-compatible request payload.
     *
     * Key differences from the base OpenAI payload:
     *   - `max_completion_tokens` replaces `max_tokens`
     *   - `response_format` is intentionally omitted because Cerebras's preview
     *     implementation of `json_object` on `gemma-4-31b` triggers constrained
     *     generation loops. Prompt-based JSON generation works with 100% reliability.
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
            // Cerebras uses `max_completion_tokens`, not `max_tokens`.
            'max_completion_tokens' => $config->maxTokens,
            'temperature' => $config->temperature,
        ];
    }
}
