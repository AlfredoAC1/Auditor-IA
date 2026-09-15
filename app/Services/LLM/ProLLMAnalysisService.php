<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Contracts\LLM\LLMAnalysisServiceInterface;
use App\Enums\AiModelTier;
use App\Enums\AnalysisType;
use App\Models\Document;
use App\Models\DocumentAnalysis;
use App\Services\LLM\Providers\LlmProviderAdapterInterface;
use App\Services\LLM\Providers\LlmProviderConfig;
use App\Services\LLM\Providers\OpenAiCompatibleAdapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Pro & Lifetime LLM Analysis Service.
 *
 * Dedicated exclusively to Pro and Lifetime subscribers.
 * Executes exclusively on Qwen Cloud (Alibaba Cloud DashScope) with
 * 4 complexity weights and prompts scaling in depth and length:
 *   - Lightweight: Qwen3 14B (4,096 tokens)
 *   - Standard:    Qwen3 Max (8,192 tokens)
 *   - Advanced:    Qwen3.5 Flash (16,384 tokens)
 *   - Premium:     Qwen-3.7 Flash (32,768 tokens)
 *
 * @see ProLlmPromptBuilder For tier-scaled prompt generation
 * @see OpenAiCompatibleAdapter For DashScope OpenAI-compatible transport
 */
final class ProLLMAnalysisService implements LLMAnalysisServiceInterface
{
    public function __construct(
        private readonly ProLlmPromptBuilder $promptBuilder,
    ) {}

    /**
     * Analyze a single document using Qwen Cloud.
     *
     * @param  string  $documentContent  Compact text from DocumentParsingService
     * @param  AnalysisType  $analysisType  Analysis type to execute
     * @param  Document  $document  Source document for context
     * @param  string|null  $overrideModel  Optional model override (slug or "qwen_cloud::model_id")
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
     * @throws \RuntimeException If the API call fails or key is missing
     */
    public function analyze(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
        ?string $overrideModel = null,
    ): array {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        [$config, $tier] = $this->resolveConfigAndTier($overrideModel);
        $this->guardApiKey($config);

        $documentContent = $this->truncateContent($documentContent, $tier);

        $systemPrompt = $this->promptBuilder->buildSystemPrompt($analysisType, $tier);
        $userPrompt = $this->promptBuilder->buildUserPrompt($documentContent, $analysisType, $document, $tier);

        Log::info('Pro LLM [qwen_cloud] analysis started.', [
            'document_id' => $document->id,
            'tier' => 'pro_lifetime',
            'model_tier' => $tier->value,
            'model' => $config->model,
            'analysis_type' => $analysisType->value,
        ]);

        $adapter = $this->resolveAdapter();
        $response = $adapter->complete($systemPrompt, $userPrompt, $config, $document->id);

        $content = $this->decodeJsonResponse($response['raw_content']);

        return [
            'content' => $content,
            'llm_provider' => 'qwen_cloud',
            'llm_model' => $config->model,
            'input_tokens' => $response['input_tokens'],
            'output_tokens' => $response['output_tokens'],
            'processing_time_ms' => $response['processing_time_ms'],
            'prompt_metadata' => [
                'system_prompt_hash' => hash('sha256', $systemPrompt),
                'analysis_type' => $analysisType->value,
                'document_type' => $document->document_type?->value,
                'model' => $config->model,
                'model_tier' => $tier->value,
                'provider' => 'qwen_cloud',
                'temperature' => $config->temperature,
                'max_tokens' => $config->maxTokens,
            ],
        ];
    }

    /**
     * Analyze a document enriched with supporting documents and prior analysis.
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
     * @throws \RuntimeException If the API call fails or key is missing
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

        [$config, $tier] = $this->resolveConfigAndTier($overrideModel);
        $this->guardApiKey($config);

        $primaryContent = $this->truncateContent(
            (string) ($primaryDocument->metadata['parsed_compact_content'] ?? ''),
            $tier,
        );

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
                'content' => $this->truncateContent($supportContent, $tier),
            ];
        }

        $systemPrompt = $this->promptBuilder->buildSystemPrompt($analysisType, $tier);
        $userPrompt = $this->promptBuilder->buildContextualUserPrompt(
            primaryDocument: $primaryDocument,
            primaryContent: $primaryContent,
            existingAnalysis: $existingAnalysis,
            supportingParts: $supportingParts,
            analysisType: $analysisType,
            tier: $tier,
        );

        Log::info('Pro LLM [qwen_cloud] contextual analysis started.', [
            'document_id' => $primaryDocument->id,
            'analysis_id' => $existingAnalysis->id,
            'tier' => 'pro_lifetime',
            'model_tier' => $tier->value,
            'model' => $config->model,
            'analysis_type' => $analysisType->value,
            'supporting_document_count' => count($supportingParts),
        ]);

        $adapter = $this->resolveAdapter();
        $response = $adapter->complete($systemPrompt, $userPrompt, $config, $primaryDocument->id);

        $content = $this->decodeJsonResponse($response['raw_content']);

        return [
            'content' => $content,
            'llm_provider' => 'qwen_cloud',
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
                'model_tier' => $tier->value,
                'provider' => 'qwen_cloud',
                'temperature' => $config->temperature,
                'max_tokens' => $config->maxTokens,
                'prior_analysis_id' => $existingAnalysis->id,
                'supporting_document_count' => count($supportingParts),
                'skipped_documents' => $skippedDocuments,
            ],
        ];
    }

    // ---------------------------------------------------------------
    // Private: Configuration & Tier Resolution
    // ---------------------------------------------------------------

    /**
     * Resolve provider configuration and model tier.
     *
     * @return array{0: LlmProviderConfig, 1: AiModelTier}
     */
    private function resolveConfigAndTier(?string $overrideModel): array
    {
        $cfg = (array) config('llm.providers.qwen_cloud', []);

        $tierModels = (array) ($cfg['tier_models'] ?? [
            'lightweight' => 'qwen3-14b',
            'standard' => 'qwen3-max',
            'advanced' => 'qwen3.5-flash',
            'premium' => 'qwen-3.7-flash',
        ]);

        $tierMaxTokens = (array) ($cfg['tier_max_tokens'] ?? [
            'lightweight' => 4096,
            'standard' => 8192,
            'advanced' => 16384,
            'premium' => 32768,
        ]);

        $modelId = null;

        if ($overrideModel !== null && str_contains($overrideModel, '::')) {
            [, $modelId] = explode('::', $overrideModel, 2);
        } elseif ($overrideModel !== null && $overrideModel !== '') {
            $modelId = $overrideModel;
        }

        // Determine tier based on model ID or slug
        $tier = $this->determineTier($modelId);

        // If no model ID supplied or resolved, use configured model for this tier
        if ($modelId === null || $modelId === '') {
            $modelId = (string) ($tierModels[$tier->value] ?? 'qwen3-max');
        }

        $maxTokens = (int) ($tierMaxTokens[$tier->value] ?? ($cfg['max_tokens'] ?? 16384));

        $config = new LlmProviderConfig(
            provider: 'qwen_cloud',
            apiKey: (string) ($cfg['api_key'] ?? ''),
            model: $modelId,
            baseUrl: (string) ($cfg['base_url'] ?? 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'),
            maxTokens: $maxTokens,
            temperature: (float) ($cfg['temperature'] ?? 0.3),
            timeoutSeconds: (int) ($cfg['timeout_seconds'] ?? 180),
            rateLimitRpm: 0,
            extraHeaders: (array) ($cfg['extra_headers'] ?? []),
        );

        return [$config, $tier];
    }

    /**
     * Map model ID or slug string to an AiModelTier enum.
     */
    private function determineTier(?string $modelIdentifier): AiModelTier
    {
        if ($modelIdentifier === null || $modelIdentifier === '') {
            return AiModelTier::Standard;
        }

        $lower = strtolower($modelIdentifier);

        // Lightweight matches
        if (str_contains($lower, '14b') || str_contains($lower, 'lightweight')) {
            return AiModelTier::Lightweight;
        }

        // Advanced matches (check flash versions, 3.5)
        if (str_contains($lower, '3.5') || str_contains($lower, '3-5') || str_contains($lower, 'advanced')) {
            return AiModelTier::Advanced;
        }

        // Premium matches (3.7 flash, premium)
        if (str_contains($lower, '3.7') || str_contains($lower, '3-7') || str_contains($lower, 'premium')) {
            return AiModelTier::Premium;
        }

        // Standard matches (max, default)
        if (str_contains($lower, 'max') || str_contains($lower, 'standard')) {
            return AiModelTier::Standard;
        }

        return AiModelTier::Standard;
    }

    /**
     * Resolve the OpenAI-compatible adapter for DashScope.
     */
    private function resolveAdapter(): LlmProviderAdapterInterface
    {
        return new OpenAiCompatibleAdapter;
    }

    // ---------------------------------------------------------------
    // Private: Helpers
    // ---------------------------------------------------------------

    /**
     * Truncate document content with headroom based on tier context window.
     */
    private function truncateContent(string $content, AiModelTier $tier): string
    {
        // Allocate character limit proportional to tier capacity
        $maxChars = match ($tier) {
            AiModelTier::Lightweight => 12000,
            AiModelTier::Standard => 24000,
            AiModelTier::Advanced => 48000,
            AiModelTier::Premium => 80000,
        };

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
                'LLM provider [qwen_cloud] API key is not configured. '.
                'Set QWEN_API_KEY in your .env file.'
            );
        }
    }

    /**
     * Decode JSON response with markdown fence removal and fallback extraction.
     *
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(string $raw): array
    {
        $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $cleaned = preg_replace('/\s*```$/m', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        $decoded = json_decode($cleaned, true);

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
            Log::warning('LLM [qwen_cloud] response was not valid JSON; storing raw text.', [
                'raw_length' => mb_strlen($raw),
                'raw_preview' => mb_substr($raw, 0, 300),
            ]);

            return [
                'raw_response' => $raw,
                'parse_error' => 'La respuesta del modelo Qwen Cloud no fue JSON válido.',
            ];
        }

        return $decoded;
    }
}
