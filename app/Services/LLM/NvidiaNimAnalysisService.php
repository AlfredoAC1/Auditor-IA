<?php

declare(strict_types=1);

namespace App\Services\LLM;

use App\Contracts\LLM\LLMAnalysisServiceInterface;
use App\Enums\AnalysisType;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Service responsible for sending document content to NVIDIA NIM
 * for legal analysis in the context of Chilean legislation.
 *
 * Tailored for NVIDIA NIM API endpoints with explicit rate-limiting
 * management to strictly adhere to 40 Requests Per Minute (RPM) limits.
 */
final class NvidiaNimAnalysisService implements LLMAnalysisServiceInterface
{
    private readonly string $apiKey;
    private readonly string $model;
    private readonly string $baseUrl;
    private readonly int $maxTokens;
    private readonly float $temperature;
    private readonly int $timeoutSeconds;
    private readonly int $rateLimitRpm;

    public function __construct()
    {
        $config = (array) config('llm.providers.nvidia_nim', []);

        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->model = (string) ($config['model'] ?? 'meta/llama-3.3-70b-instruct');
        $this->baseUrl = (string) ($config['base_url'] ?? 'https://integrate.api.nvidia.com/v1');
        $this->maxTokens = (int) ($config['max_tokens'] ?? 4096);
        $this->temperature = (float) ($config['temperature'] ?? 0.3);
        $this->timeoutSeconds = (int) ($config['timeout_seconds'] ?? 180);
        $this->rateLimitRpm = (int) ($config['rate_limit_rpm'] ?? 40);
    }

    /**
     * Analyze document content using NVIDIA NIM endpoint with 40 RPM rate limiting.
     *
     * @param  string  $documentContent  Compact text from DocumentParsingService
     * @param  AnalysisType  $analysisType  Type of legal analysis to execute
     * @param  Document  $document  Document instance for contextual tracing
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
     * @throws \RuntimeException If API key missing, rate limit reached, or request fails
     */
    public function analyze(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
        ?string $overrideModel = null,
    ): array {
        $activeModel = $overrideModel ?? $this->model;
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        if ($this->apiKey === '') {
            throw new \RuntimeException('NVIDIA NIM API key is not configured. Set NVIDIA_NIM_API_KEY in .env.');
        }

        // 1. Enforce 40 Requests Per Minute (RPM) rate limiting
        $this->enforceRateLimit($document->id);

        // 2. Safeguard document content length to avoid exceeding context window
        $maxChunkChars = ((int) config('llm.analysis.max_chunk_tokens', 4000)) * 4;
        if (mb_strlen($documentContent) > $maxChunkChars) {
            $documentContent = mb_substr($documentContent, 0, $maxChunkChars) . "\n\n[... TEXTO TRUNCADO POR EXTENSIÓN PARA EL MODELO ...]";
        }

        $systemPrompt = $this->buildSystemPrompt($analysisType);
        $userPrompt = $this->buildUserPrompt($documentContent, $analysisType, $document);

        $startTime = microtime(true);

        Log::info('NVIDIA NIM analysis started.', [
            'document_id' => $document->id,
            'analysis_type' => $analysisType->value,
            'model' => $this->model,
            'content_length' => mb_strlen($documentContent),
            'rate_limit_rpm' => $this->rateLimitRpm,
        ]);

        // 3. Increment rate limiter hit counter before executing request
        $rateLimitKey = $this->getRateLimitKey();
        RateLimiter::hit($rateLimitKey, 60);

        $endpoint = rtrim($this->baseUrl, '/') . '/chat/completions';

        $response = Http::timeout($this->timeoutSeconds)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post($endpoint, [
                'model' => $activeModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => 1.0,
                'response_format' => ['type' => 'json_object'],
            ]);

        $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        // 4. Handle errors and Rate Limit (HTTP 429 Too Many Requests)
        if ($response->failed()) {
            $status = $response->status();
            $errorBody = $response->body();

            Log::error('NVIDIA NIM API request failed.', [
                'document_id' => $document->id,
                'status' => $status,
                'model' => $this->model,
                'error' => mb_substr($errorBody, 0, 500),
            ]);

            if ($status === 429 || str_contains($errorBody, 'rate limit') || str_contains($errorBody, 'Too Many Requests')) {
                // Record extra hit to slow down aggressive retries
                RateLimiter::hit($rateLimitKey, 60);
                $retryAfter = $response->header('Retry-After');
                $waitMsg = $retryAfter ? " Retry after {$retryAfter} seconds." : '';

                throw new \RuntimeException(
                    "NVIDIA NIM Rate limit reached (40 RPM max limit).{$waitMsg} Status [429]."
                );
            }

            if (str_contains($errorBody, 'max completion tokens reached') || str_contains($errorBody, 'length')) {
                throw new \RuntimeException(
                    "NVIDIA NIM token limit reached for model [{$this->model}] (max_tokens={$this->maxTokens}). Increase NVIDIA_NIM_MAX_TOKENS in .env."
                );
            }

            $decodedError = json_decode($errorBody, true);
            $errorMessage = $decodedError['detail'] ?? $decodedError['error']['message'] ?? $errorBody;

            throw new \RuntimeException(
                "NVIDIA NIM API returned status [{$status}]: " . mb_substr((string) $errorMessage, 0, 200)
            );
        }

        $data = $response->json();
        $content = $this->extractAnalysisContent($data);
        $usage = $data['usage'] ?? [];

        Log::info('NVIDIA NIM analysis completed.', [
            'document_id' => $document->id,
            'analysis_type' => $analysisType->value,
            'input_tokens' => $usage['prompt_tokens'] ?? 0,
            'output_tokens' => $usage['completion_tokens'] ?? 0,
            'processing_time_ms' => $processingTimeMs,
        ]);

        return [
            'content' => $content,
            'llm_provider' => 'nvidia_nim',
            'llm_model' => $activeModel,
            'input_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
            'output_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'processing_time_ms' => $processingTimeMs,
            'prompt_metadata' => [
                'system_prompt_hash' => hash('sha256', $systemPrompt),
                'analysis_type' => $analysisType->value,
                'document_type' => $document->document_type?->value,
                'model' => $this->model,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'rate_limit_rpm' => $this->rateLimitRpm,
            ],
        ];
    }

    /**
     * Enforce rate limits (max 40 RPM by default).
     *
     * @throws \RuntimeException If rate limit is exceeded
     */
    private function enforceRateLimit(?string $documentId = null): void
    {
        $key = $this->getRateLimitKey();

        if (RateLimiter::tooManyAttempts($key, $this->rateLimitRpm)) {
            $seconds = RateLimiter::availableIn($key);

            Log::warning('NVIDIA NIM rate limit reached locally.', [
                'document_id' => $documentId,
                'limit_rpm' => $this->rateLimitRpm,
                'seconds_to_wait' => $seconds,
            ]);

            // Short pause if available within 2 seconds
            if ($seconds > 0 && $seconds <= 2) {
                usleep($seconds * 1_000_000);
                if (! RateLimiter::tooManyAttempts($key, $this->rateLimitRpm)) {
                    return;
                }
            }

            throw new \RuntimeException(
                "NVIDIA NIM RPM limit exceeded ({$this->rateLimitRpm} requests per minute limit reached). Please wait {$seconds} second(s) before retrying."
            );
        }
    }

    /**
     * Get rate limit cache key.
     */
    private function getRateLimitKey(): string
    {
        return 'llm:nvidia_nim:rate_limit';
    }

    /**
     * Build system prompt for Chilean legislation context.
     */
    private function buildSystemPrompt(AnalysisType $analysisType): string
    {
        $baseContext = <<<'PROMPT'
Eres un asistente legal especializado en legislación chilena. Tu rol es analizar documentos legales y proporcionar información clara, precisa y útil para personas sin formación jurídica.

CONTEXTO LEGAL:
- Todas tus respuestas deben basarse en el marco legal chileno vigente.
- Referencia leyes específicas cuando sea relevante (Código Civil, Código del Trabajo, Ley de Protección al Consumidor, etc.).
- Si identificas cláusulas que podrían ser abusivas según la legislación chilena, menciónalo explícitamente.
- Indica siempre cuando una situación requiere consulta profesional con un abogado.

REGLAS DE RESPUESTA:
- Responde SIEMPRE en español chileno.
- Usa lenguaje simple y claro, evitando jerga legal innecesaria.
- Cuando uses términos legales, explícalos brevemente entre paréntesis.
- Estructura tu respuesta estrictamente en el formato JSON especificado.
- Sé conciso y directo en cada valor del JSON. Limita los arreglos a un máximo de 10 ítems significativos.
- Mantén las explicaciones breves (1-2 oraciones por campo) para asegurar que el JSON se genere completo sin alcanzar el límite de tokens.
- NO inventes leyes ni artículos que no existan.
- Si no tienes certeza sobre un punto legal, indícalo explícitamente.
- NUNCA des asesoría legal definitiva — siempre recomienda consultar con un abogado para decisiones importantes.
PROMPT;

        $specificInstructions = match ($analysisType) {
            AnalysisType::FullSummary => <<<'PROMPT'

TIPO DE ANÁLISIS: Resumen Completo
Proporciona un análisis integral del documento que incluya todas las secciones.
Responde en formato JSON con la siguiente estructura:
{
  "resumen_general": "Descripción general del documento en 2-3 párrafos",
  "tipo_documento": "Tipo de documento identificado",
  "partes_involucradas": ["Lista de partes mencionadas en el documento"],
  "clausulas_principales": [
    {
      "numero": "Número o identificador de la cláusula",
      "titulo": "Título o tema de la cláusula",
      "explicacion": "Explicación en lenguaje simple",
      "implicacion_legal": "Qué significa legalmente para las partes"
    }
  ],
  "riesgos_identificados": [
    {
      "riesgo": "Descripción del riesgo",
      "severidad": "alta|media|baja",
      "explicacion": "Por qué es un riesgo",
      "referencia_legal": "Ley o artículo relevante de la legislación chilena"
    }
  ],
  "recomendaciones": [
    {
      "recomendacion": "Acción sugerida",
      "prioridad": "alta|media|baja",
      "justificacion": "Por qué se recomienda esto"
    }
  ],
  "leyes_aplicables": ["Lista de leyes chilenas relevantes al documento"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo",
  "notas_adicionales": "Cualquier observación adicional relevante"
}
PROMPT,
            AnalysisType::ClauseAnalysis => <<<'PROMPT'

TIPO DE ANÁLISIS: Análisis de Cláusulas
Analiza cada cláusula del documento individualmente.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "total_clausulas": 0,
  "clausulas": [
    {
      "numero": "Número o identificador",
      "titulo": "Tema de la cláusula",
      "texto_original_resumido": "Resumen breve del texto original",
      "explicacion_simple": "Qué significa en lenguaje cotidiano",
      "es_estandar": true,
      "es_potencialmente_abusiva": false,
      "referencia_legal": "Ley chilena aplicable si corresponde",
      "alerta": "Punto de atención si existe, null si no"
    }
  ],
  "clausulas_faltantes": ["Cláusulas que deberían estar pero no aparecen, según la legislación chilena"],
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,
            AnalysisType::RiskAssessment => <<<'PROMPT'

TIPO DE ANÁLISIS: Evaluación de Riesgos
Identifica y evalúa los riesgos legales presentes en el documento.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "nivel_riesgo_general": "alto|medio|bajo",
  "riesgos": [
    {
      "id": 1,
      "titulo": "Título del riesgo",
      "descripcion": "Descripción detallada",
      "severidad": "alta|media|baja",
      "probabilidad": "alta|media|baja",
      "clausula_relacionada": "Referencia a la cláusula del documento",
      "referencia_legal": "Ley chilena que aplica",
      "recomendacion": "Qué hacer al respecto",
      "requiere_accion_inmediata": false
    }
  ],
  "fortalezas": ["Aspectos positivos del documento"],
  "debilidades": ["Aspectos que necesitan atención"],
  "recomendacion_general": "Resumen de qué debería hacer el usuario",
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,
            AnalysisType::Recommendation => <<<'PROMPT'

TIPO DE ANÁLISIS: Recomendaciones
Proporciona recomendaciones prácticas basadas en el contenido del documento.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "resumen_breve": "Resumen del documento en 1-2 oraciones",
  "recomendaciones": [
    {
      "id": 1,
      "titulo": "Título de la recomendación",
      "descripcion": "Explicación detallada de la recomendación",
      "prioridad": "alta|media|baja",
      "categoria": "legal|financiero|administrativo|proteccion",
      "accion_sugerida": "Paso concreto que el usuario debería tomar",
      "referencia_legal": "Ley chilena relevante si aplica"
    }
  ],
  "puntos_a_negociar": ["Puntos que podrían negociarse antes de firmar"],
  "documentos_complementarios": ["Documentos adicionales que el usuario debería solicitar"],
  "requiere_abogado": true,
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,
            AnalysisType::Explanation => <<<'PROMPT'

TIPO DE ANÁLISIS: Explicación Simple
Explica el documento en lenguaje cotidiano para una persona sin conocimientos legales.
Responde en formato JSON con la siguiente estructura:
{
  "tipo_documento": "Tipo de documento identificado",
  "explicacion_general": "Explicación del documento como si se lo explicaras a un amigo, en 2-3 párrafos",
  "que_significa_para_ti": "Qué implica este documento para el usuario en términos prácticos",
  "puntos_clave": [
    {
      "punto": "Punto importante",
      "explicacion": "Qué significa en la práctica"
    }
  ],
  "obligaciones_del_usuario": ["Lista de obligaciones que asume quien firma"],
  "derechos_del_usuario": ["Lista de derechos que tiene quien firma"],
  "fechas_importantes": [
    {
      "fecha_o_plazo": "Fecha o plazo mencionado",
      "que_sucede": "Qué pasa en esa fecha"
    }
  ],
  "montos_o_valores": [
    {
      "concepto": "Concepto del monto",
      "valor": "Valor mencionado",
      "nota": "Nota relevante si aplica"
    }
  ],
  "preguntas_frecuentes": [
    {
      "pregunta": "Pregunta que el usuario podría tener",
      "respuesta": "Respuesta clara y simple"
    }
  ],
  "nivel_confianza": "alto|medio|bajo"
}
PROMPT,
        };

        return $baseContext . $specificInstructions;
    }

    /**
     * Build user prompt with document context.
     */
    private function buildUserPrompt(
        string $documentContent,
        AnalysisType $analysisType,
        Document $document,
    ): string {
        $contextParts = [];

        if ($document->document_type !== null) {
            $contextParts[] = "Tipo de documento declarado: {$document->document_type->label()}";
        }

        if ($document->original_filename) {
            $contextParts[] = "Nombre del archivo: {$document->original_filename}";
        }

        $contextHeader = $contextParts !== []
            ? "CONTEXTO:\n" . implode("\n", $contextParts) . "\n\n"
            : '';

        return <<<PROMPT
{$contextHeader}DOCUMENTO A ANALIZAR:

{$documentContent}

Por favor, realiza un análisis de tipo "{$analysisType->label()}" de este documento, siguiendo estrictamente el formato JSON indicado en las instrucciones del sistema.
PROMPT;
    }

    /**
     * Extract structured analysis content from NVIDIA NIM API response.
     *
     * @param  array<string, mixed>  $responseData
     * @return array<string, mixed>
     *
     * @throws \RuntimeException If response structure is missing expected keys
     */
    private function extractAnalysisContent(array $responseData): array
    {
        $message = $responseData['choices'][0]['message']['content'] ?? null;

        if ($message === null) {
            throw new \RuntimeException('NVIDIA NIM API response does not contain expected message content.');
        }

        $decoded = json_decode($message, true);

        if (! is_array($decoded)) {
            Log::warning('NVIDIA NIM response was not valid JSON, wrapping as raw text.', [
                'raw_length' => mb_strlen($message),
            ]);

            return [
                'raw_response' => $message,
                'parse_error' => 'La respuesta del modelo no fue JSON válido.',
            ];
        }

        return $decoded;
    }
}
