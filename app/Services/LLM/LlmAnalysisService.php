<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Contracts\LLM\LLMAnalysisServiceInterface;
use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\LLM\Providers\CerebrasAdapter;
use App\Services\LLM\Providers\LlmProviderAdapterInterface;
use App\Services\LLM\Providers\LlmProviderConfig;
use App\Services\LLM\Providers\OpenAiCompatibleAdapter;
use App\Services\LLM\Providers\RateLimitedAdapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Free Tier LLM Analysis Service.
 *
 * Dedicated exclusively to Free tier document analysis.
 * Operates exclusively on Cerebras Inference with GPT-OSS 20B,
 * strictly separated from Pro & Lifetime pipelines.
 *
 * @see LlmPromptBuilder For free tier prompt construction
 * @see CerebrasAdapter For Cerebras inference transport
 */
final class LlmAnalysisService implements LLMAnalysisServiceInterface
{
    public function __construct(
        private readonly LlmPromptBuilder $promptBuilder,
    ) {}

    // ---------------------------------------------------------------
    // Public API — mirrors the old GroqAnalysisService interface
    // ---------------------------------------------------------------

    /**
     * Analyze a single document.
     *
     * @param  string  $documentContent  Compact text from DocumentParsingService
     * @param  AnalysisType  $analysisType  Analysis type to perform
     * @param  Document  $document  Source document (for context metadata)
     * @param  string|null  $overrideModel  Full provider model ID to override config default (format: "provider::model_id")
     * @return array{
     *     content: array<string, mixed>,
     *     llm_provider: string,
     *     llm_model: string,
     *     input_tokens: int,
     *     output_tokens: int,
     *     processing_time_ms: int,
     *     prompt_metadata: array<string, mixed>,
     * }
     *
     * @throws \RuntimeException If the provider API call fails
     */
    public function analyze(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
        ?string $overrideModel = null,
    ): array {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $config = $this->resolveConfig($overrideModel);
        $this->guardApiKey($config);

        $documentContent = $this->truncateContent($documentContent);

        $systemPrompt = $this->promptBuilder->buildSystemPrompt($analysisType);
        $userPrompt = $this->promptBuilder->buildUserPrompt($documentContent, $analysisType, $document);

        $adapter = $this->resolveAdapter($config);
        $response = $adapter->complete($systemPrompt, $userPrompt, $config, $document->id);

        $content = $this->decodeJsonResponse($response['raw_content'], $config->provider);

        return [
            'content' => $content,
            'llm_provider' => $config->provider,
            'llm_model' => $config->model,
            'input_tokens' => $response['input_tokens'],
            'output_tokens' => $response['output_tokens'],
            'processing_time_ms' => $response['processing_time_ms'],
            'prompt_metadata' => [
                'system_prompt_hash' => hash('sha256', $systemPrompt),
                'analysis_type' => $analysisType->value,
                'document_type' => $document->document_type?->value,
                'model' => $config->model,
                'provider' => $config->provider,
                'temperature' => $config->temperature,
                'max_tokens' => $config->maxTokens,
            ],
        ];
    }

    /**
     * Analyze a document enriched with supporting documents and a prior analysis.
     *
     * @param  Collection<int, Document>  $supportingDocuments
     * @return array{
     *     content: array<string, mixed>,
     *     llm_provider: string,
     *     llm_model: string,
     *     input_tokens: int,
     *     output_tokens: int,
     *     processing_time_ms: int,
     *     prompt_metadata: array<string, mixed>,
     *     skipped_documents: array<string>,
     * }
     *
     * @throws \RuntimeException If the provider API call fails
     */
    public function analyzeWithContext(
        Document $primaryDocument,
        DocumentAnalysis $existingAnalysis,
        Collection $supportingDocuments,
        AnalysisType $analysisType = AnalysisType::ContextualSummary,
        ?string $overrideModel = null,
    ): array {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $config = $this->resolveConfig($overrideModel);
        $this->guardApiKey($config);

        // Primary document
        $primaryContent = $this->truncateContent(
            (string) ($primaryDocument->metadata['parsed_compact_content'] ?? '')
        );

        // Supporting documents
        $supportingParts = [];
        $skippedDocuments = [];

        foreach ($supportingDocuments as $supportDoc) {
            $supportContent = (string) ($supportDoc->metadata['parsed_compact_content'] ?? '');

            if ($supportContent === '') {
                $skippedDocuments[] = $supportDoc->original_filename;
                Log::warning('Supporting document skipped — not yet parsed.', [
                    'document_id' => $supportDoc->id,
                    'filename' => $supportDoc->original_filename,
                ]);

                continue;
            }

            $supportingParts[] = [
                'filename' => $supportDoc->original_filename,
                'content' => $this->truncateContent($supportContent),
            ];
        }

        $systemPrompt = $this->promptBuilder->buildSystemPrompt($analysisType);
        $userPrompt = $this->promptBuilder->buildContextualUserPrompt(
            primaryDocument: $primaryDocument,
            primaryContent: $primaryContent,
            existingAnalysis: $existingAnalysis,
            supportingParts: $supportingParts,
            analysisType: $analysisType,
        );

        Log::info("LLM [{$config->provider}] contextual analysis started.", [
            'document_id' => $primaryDocument->id,
            'analysis_id' => $existingAnalysis->id,
            'analysis_type' => $analysisType->value,
            'supporting_document_count' => count($supportingParts),
            'skipped_document_count' => count($skippedDocuments),
            'model' => $config->model,
        ]);

        $adapter = $this->resolveAdapter($config);
        $response = $adapter->complete($systemPrompt, $userPrompt, $config, $primaryDocument->id);

        $content = $this->decodeJsonResponse($response['raw_content'], $config->provider);

        return [
            'content' => $content,
            'llm_provider' => $config->provider,
            'llm_model' => $config->model,
            'input_tokens' => $response['input_tokens'],
            'output_tokens' => $response['output_tokens'],
            'processing_time_ms' => $response['processing_time_ms'],
            'skipped_documents' => $skippedDocuments,
            'prompt_metadata' => [
                'system_prompt_hash' => hash('sha256', $systemPrompt),
                'analysis_type' => $analysisType->value,
                'document_type' => $primaryDocument->document_type?->value,
                'model' => $config->model,
                'provider' => $config->provider,
                'temperature' => $config->temperature,
                'max_tokens' => $config->maxTokens,
                'prior_analysis_id' => $existingAnalysis->id,
                'supporting_document_count' => count($supportingParts),
                'skipped_documents' => $skippedDocuments,
            ],
        ];
    }

    // ---------------------------------------------------------------
    // Private: config resolution
    // ---------------------------------------------------------------

    /**
     * Resolve a LlmProviderConfig from an override string or the default provider.
     *
     * Override format: "provider::model_id"  (e.g. "groq::llama-3.1-8b-instant")
     * If only a model ID is supplied (no "::" separator), the default provider is
     * used with the model substituted.
     */
    private function resolveConfig(?string $overrideModel): LlmProviderConfig
    {
        $defaultProvider = 'cerebras';

        if ($overrideModel !== null && str_contains($overrideModel, '::')) {
            [$provider, $modelId] = explode('::', $overrideModel, 2);
        } else {
            $provider = $defaultProvider;
            $modelId = $overrideModel; // null means use config default
        }

        $providerKey = $this->normaliseProviderKey($provider);

        // Enforce Cerebras in production for Free tier isolation
        if ($providerKey !== 'cerebras' && ! app()->environment('testing')) {
            Log::warning("Unauthorized provider [{$providerKey}] requested in Free Tier LLM service. Enforcing [cerebras].", [
                'requested_provider' => $provider,
            ]);
            $providerKey = 'cerebras';
            $modelId = null;
        }

        $cfg = (array) config("llm.providers.{$providerKey}", []);

        if ($cfg === []) {
            Log::warning("LLM provider [{$providerKey}] has no configuration. Falling back to [{$defaultProvider}].", [
                'requested_provider' => $provider,
            ]);
            $providerKey = $this->normaliseProviderKey($defaultProvider);
            $cfg = (array) config("llm.providers.{$providerKey}", []);
        }

        return new LlmProviderConfig(
            provider: $providerKey,
            apiKey: (string) ($cfg['api_key'] ?? ''),
            model: $modelId ?? (string) ($cfg['model'] ?? 'gpt-oss-20b'),
            baseUrl: (string) ($cfg['base_url'] ?? ''),
            maxTokens: (int) ($cfg['max_tokens'] ?? 8192),
            temperature: (float) ($cfg['temperature'] ?? 0.3),
            timeoutSeconds: (int) ($cfg['timeout_seconds'] ?? 180),
            rateLimitRpm: (int) ($cfg['rate_limit_rpm'] ?? 0),
            extraHeaders: (array) ($cfg['extra_headers'] ?? []),
        );
    }

    /**
     * Normalise provider aliases to canonical config keys.
     *
     * Allows "nvidia" → "nvidia_nim", "qwen" → "qwen_cloud", etc.
     */
    private function normaliseProviderKey(string $provider): string
    {
        return match (strtolower($provider)) {
            'nvidia', 'nvidia_nim' => 'nvidia_nim',
            'qwen', 'qwen_cloud' => 'qwen_cloud',
            default => strtolower($provider),
        };
    }

    // ---------------------------------------------------------------
    // Private: adapter resolution
    // ---------------------------------------------------------------

    /**
     * Resolve the transport adapter for a given provider config.
     *
     * Uses the rate-limited adapter for providers with rateLimitRpm > 0,
     * and the generic OpenAI-compatible adapter for everything else.
     *
     * To add a fully custom adapter (e.g. for a provider with a different
     * API structure), add a new case here and register the class.
     */
    private function resolveAdapter(LlmProviderConfig $config): LlmProviderAdapterInterface
    {
        // Providers with enforced RPM caps get the rate-limited adapter
        if ($config->rateLimitRpm > 0) {
            return new RateLimitedAdapter;
        }

        // Cerebras requires `max_completion_tokens` instead of `max_tokens`
        if ($config->provider === 'cerebras') {
            return new CerebrasAdapter;
        }

        // All other OpenAI-compatible providers
        return new OpenAiCompatibleAdapter;
    }

    // ---------------------------------------------------------------
    // Private: helpers
    // ---------------------------------------------------------------

    /**
     * Safeguard document content length to avoid overflowing context windows.
     */
    private function truncateContent(string $content): string
    {
        $maxChars = ((int) config('llm.analysis.max_chunk_tokens', 2000)) * 4;

        if (mb_strlen($content) > $maxChars) {
            return mb_substr($content, 0, $maxChars)."\n\n[... TEXTO TRUNCADO POR EXTENSIÓN PARA EL MODELO ...]";
        }

        return $content;
    }

    /**
     * Guard that the resolved API key is not empty.
     *
     * @throws \RuntimeException
     */
    private function guardApiKey(LlmProviderConfig $config): void
    {
        if ($config->apiKey === '') {
            throw new \RuntimeException(
                "LLM provider [{$config->provider}] API key is not configured. ".
                'Set the corresponding *_API_KEY variable in your .env file.'
            );
        }
    }

    /**
     * Decode raw JSON string from the LLM into an array.
     *
     * If the model returns invalid JSON (hallucinated markdown fences, etc.),
     * the raw text is wrapped rather than throwing an exception so the
     * analysis record is still stored for human review.
     *
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(string $raw, string $provider): array
    {
        // Strip common markdown code fences that some models include
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $cleaned = preg_replace('/\s*```$/m', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        $decoded = json_decode($cleaned, true);

        // If direct decode failed, try extracting JSON substring between first { and last }
        if (! is_array($decoded)) {
            $firstBrace = strpos($raw, '{');
            $lastBrace = strrpos($raw, '}');
            if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                $subJson = substr($raw, $firstBrace, $lastBrace - $firstBrace + 1);
                $subDecoded = json_decode($subJson, true);
                if (is_array($subDecoded)) {
                    $decoded = $subDecoded;
                }
            }
        }

        if (! is_array($decoded)) {
            Log::warning("LLM [{$provider}] response was not valid JSON; storing raw text.", [
                'raw_length' => mb_strlen($raw),
                'raw_preview' => mb_substr($raw, 0, 300),
            ]);

            return [
                'raw_response' => $raw,
                'parse_error' => 'La respuesta del modelo no fue JSON válido.',
            ];
        }

        return $decoded;
    }
}
