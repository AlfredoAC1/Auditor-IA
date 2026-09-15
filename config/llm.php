<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | The provider used when no model_slug / override is specified.
    | Supported keys: "groq", "cerebras", "qwen_cloud", "nvidia_nim",
    |                  "deepseek", "azure"
    |
    */

    'default_provider' => env('LLM_PROVIDER', 'cerebras'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Keys must match AiModelSeeder provider slugs and the normaliseProviderKey()
    | map inside LlmAnalysisService.
    |
    | rate_limit_rpm: set > 0 to activate the RateLimitedAdapter for
    |                 providers with strict per-minute request caps.
    |
    | extra_headers:  any additional headers to merge into every request
    |                 (e.g. X-API-Key, X-Model-Version, etc.)
    |
    */

    'providers' => [

        // ---------------------------------------------------------------
        // Groq — OpenAI-compatible, no enforced RPM (free tier)
        // ---------------------------------------------------------------
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'max_tokens' => (int) env('GROQ_MAX_TOKENS', 8192),
            'temperature' => (float) env('GROQ_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('GROQ_TIMEOUT', 180),
            'rate_limit_rpm' => 0,
            'extra_headers' => [],
        ],

        // ---------------------------------------------------------------
        // Cerebras — OpenAI-compatible, fast inference (Free tier exclusive)
        // Docs: https://inference-docs.cerebras.ai
        // ---------------------------------------------------------------
        'cerebras' => [
            'api_key' => env('CEREBRAS_API_KEY'),
            'model' => env('CEREBRAS_MODEL', 'gpt-oss-20b'),
            'base_url' => env('CEREBRAS_BASE_URL', 'https://api.cerebras.ai/v1'),
            'max_tokens' => (int) env('CEREBRAS_MAX_TOKENS', 8192),
            'temperature' => (float) env('CEREBRAS_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('CEREBRAS_TIMEOUT', 180),
            'rate_limit_rpm' => 0,
            'extra_headers' => [],
        ],

        // ---------------------------------------------------------------
        // Qwen Cloud — OpenAI-compatible via DashScope endpoint (Pro & Lifetime exclusive)
        // Docs: https://www.alibabacloud.com/help/en/model-studio
        // ---------------------------------------------------------------
        'qwen_cloud' => [
            // Bridge: supports both QWEN_API_KEY and legacy QWEN_CLOUD_API_KEY naming.
            'api_key' => env('QWEN_API_KEY', env('QWEN_CLOUD_API_KEY')),
            'model' => env('QWEN_MODEL', 'qwen3-max'),
            // DashScope international endpoint (OpenAI-compatible mode).
            'base_url' => env('QWEN_BASE_URL', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'),
            'max_tokens' => (int) env('QWEN_MAX_TOKENS', 16384),
            'temperature' => (float) env('QWEN_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('QWEN_TIMEOUT', 180),
            'rate_limit_rpm' => 0,
            'extra_headers' => [],
            // Model tier mapping for Pro & Lifetime users
            'tier_models' => [
                'lightweight' => env('QWEN_MODEL_LIGHTWEIGHT', 'qwen3-14b'),
                'standard' => env('QWEN_MODEL_STANDARD', 'qwen3-max'),
                'advanced' => env('QWEN_MODEL_ADVANCED', 'qwen3.5-flash'),
                'premium' => env('QWEN_MODEL_PREMIUM', 'qwen-3.7-flash'),
            ],
            // Tier max token budgets
            'tier_max_tokens' => [
                'lightweight' => (int) env('QWEN_MAX_TOKENS_LIGHTWEIGHT', 4096),
                'standard' => (int) env('QWEN_MAX_TOKENS_STANDARD', 8192),
                'advanced' => (int) env('QWEN_MAX_TOKENS_ADVANCED', 16384),
                'premium' => (int) env('QWEN_MAX_TOKENS_PREMIUM', 32768),
            ],
        ],

        // ---------------------------------------------------------------
        // NVIDIA NIM — OpenAI-compatible, strict 40 RPM cap
        // ---------------------------------------------------------------
        'nvidia_nim' => [
            'api_key' => env('NVIDIA_NIM_API_KEY'),
            'model' => env('NVIDIA_NIM_MODEL', 'meta/llama-3.3-70b-instruct'),
            'base_url' => env('NVIDIA_NIM_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'max_tokens' => (int) env('NVIDIA_NIM_MAX_TOKENS', 4096),
            'temperature' => (float) env('NVIDIA_NIM_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('NVIDIA_NIM_TIMEOUT', 180),
            'rate_limit_rpm' => (int) env('NVIDIA_NIM_RPM', 40),
            'extra_headers' => [],
        ],

        // ---------------------------------------------------------------
        // DeepSeek — OpenAI-compatible
        // ---------------------------------------------------------------
        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
            'max_tokens' => (int) env('DEEPSEEK_MAX_TOKENS', 4096),
            'temperature' => (float) env('DEEPSEEK_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('DEEPSEEK_TIMEOUT', 120),
            'rate_limit_rpm' => 0,
            'extra_headers' => [],
        ],

        // ---------------------------------------------------------------
        // Azure OpenAI — Note: uses a different URL pattern per deployment.
        // base_url should be set to the full endpoint including deployment:
        //   https://{resource}.openai.azure.com/openai/deployments/{deployment}
        // ---------------------------------------------------------------
        'azure' => [
            'api_key' => env('AZURE_OPENAI_KEY'),
            'model' => env('AZURE_OPENAI_DEPLOYMENT', 'gpt-4'),
            'base_url' => env('AZURE_OPENAI_ENDPOINT'),
            'max_tokens' => (int) env('AZURE_OPENAI_MAX_TOKENS', 4096),
            'temperature' => (float) env('AZURE_OPENAI_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('AZURE_OPENAI_TIMEOUT', 120),
            'rate_limit_rpm' => 0,
            'extra_headers' => [
                'api-version' => env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'),
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Configuration
    |--------------------------------------------------------------------------
    */

    'analysis' => [
        'queue' => env('LLM_ANALYSIS_QUEUE', 'analysis'),
        'max_retries' => (int) env('LLM_ANALYSIS_MAX_RETRIES', 3),
        'retry_backoff_seconds' => [30, 120, 600],
        'max_chunk_tokens' => (int) env('LLM_MAX_CHUNK_TOKENS', 2000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking (USD per 1K tokens — update as pricing changes)
    |--------------------------------------------------------------------------
    */

    'cost_per_1k_tokens' => [
        'groq' => ['input' => 0.0,     'output' => 0.0],
        'cerebras' => ['input' => 0.0001,  'output' => 0.0001],
        'qwen_cloud' => ['input' => 0.0004,  'output' => 0.0012],
        'nvidia_nim' => ['input' => 0.0,     'output' => 0.0],
        'deepseek' => ['input' => 0.00014, 'output' => 0.00028],
        'azure' => ['input' => 0.0015,  'output' => 0.002],
    ],

];
