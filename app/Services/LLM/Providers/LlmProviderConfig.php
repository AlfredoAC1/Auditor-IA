<?php

declare(strict_types=1);

namespace App\Services\LLM\Providers;

/**
 * Immutable configuration for a single LLM provider connection.
 *
 * Populated from config/llm.php — one instance per resolved provider.
 */
final readonly class LlmProviderConfig
{
    public function __construct(
        /** Internal provider slug (e.g. 'groq', 'cerebras', 'qwen'). */
        public string $provider,
        public string $apiKey,
        public string $model,
        public string $baseUrl,
        public int $maxTokens,
        public float $temperature,
        public int $timeoutSeconds,
        /** Optional RPM cap; 0 means no enforced rate limiting. */
        public int $rateLimitRpm = 0,
        /** Extra headers to merge into every request (e.g. custom auth schemes). */
        public array $extraHeaders = [],
    ) {}
}
