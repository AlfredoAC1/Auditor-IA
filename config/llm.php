<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | The default LLM provider to use for document analysis.
    | Supported: "groq", "deepseek", "azure"
    |
    | Start with "groq" for testing, migrate to "deepseek" or "azure"
    | for production.
    |
    */

    'default_provider' => env('LLM_PROVIDER', 'groq'),

    /*
    |--------------------------------------------------------------------------
    | LLM Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Each provider has its own configuration. The application uses
    | LLMProviderInterface to abstract provider differences.
    |
    */

    'providers' => [

        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'max_tokens' => (int) env('GROQ_MAX_TOKENS', 8192),
            'temperature' => (float) env('GROQ_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('GROQ_TIMEOUT', 180),
        ],

        'nvidia_nim' => [
            'api_key' => env('NVIDIA_NIM_API_KEY'),
            'model' => env('NVIDIA_NIM_MODEL', 'meta/llama-3.3-70b-instruct'),
            'base_url' => env('NVIDIA_NIM_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'max_tokens' => (int) env('NVIDIA_NIM_MAX_TOKENS', 4096),
            'temperature' => (float) env('NVIDIA_NIM_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('NVIDIA_NIM_TIMEOUT', 180),
            'rate_limit_rpm' => (int) env('NVIDIA_NIM_RPM', 40),
        ],

        'deepseek' => [
            'api_key' => env('DEEPSEEK_API_KEY'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com/v1'),
            'max_tokens' => (int) env('DEEPSEEK_MAX_TOKENS', 4096),
            'temperature' => (float) env('DEEPSEEK_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('DEEPSEEK_TIMEOUT', 120),
        ],

        'azure' => [
            'api_key' => env('AZURE_OPENAI_KEY'),
            'endpoint' => env('AZURE_OPENAI_ENDPOINT'),
            'deployment' => env('AZURE_OPENAI_DEPLOYMENT'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'),
            'max_tokens' => (int) env('AZURE_OPENAI_MAX_TOKENS', 4096),
            'temperature' => (float) env('AZURE_OPENAI_TEMPERATURE', 0.3),
            'timeout_seconds' => (int) env('AZURE_OPENAI_TIMEOUT', 120),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for document analysis operations.
    |
    */

    'analysis' => [
        'queue' => env('LLM_ANALYSIS_QUEUE', 'analysis'),
        'max_retries' => (int) env('LLM_ANALYSIS_MAX_RETRIES', 3),
        'retry_backoff_seconds' => [30, 120, 600],
        'max_chunk_tokens' => (int) env('LLM_MAX_CHUNK_TOKENS', 2000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Approximate cost per 1K tokens for each provider (in USD).
    | Used for usage reporting and billing estimates.
    | Update these as provider pricing changes.
    |
    */

    'cost_per_1k_tokens' => [
        'groq' => [
            'input' => 0.0,   // Groq free tier during testing
            'output' => 0.0,
        ],
        'nvidia_nim' => [
            'input' => 0.0,   // NVIDIA NIM API free tier credits / self-hosted
            'output' => 0.0,
        ],
        'deepseek' => [
            'input' => 0.00014,
            'output' => 0.00028,
        ],
        'azure' => [
            'input' => 0.0015,
            'output' => 0.002,
        ],
    ],

];
