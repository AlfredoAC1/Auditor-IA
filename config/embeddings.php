<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Embedding Provider
    |--------------------------------------------------------------------------
    |
    | The provider used for generating embeddings for document chunks
    | and Chilean law articles. Must produce vectors matching the
    | dimension configured below.
    |
    */

    'provider' => env('EMBEDDING_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Embedding Model
    |--------------------------------------------------------------------------
    |
    | The specific model to use for embedding generation.
    | Make sure the dimension matches the model's output.
    |
    */

    'model' => env('EMBEDDING_MODEL', 'text-embedding-3-small'),

    /*
    |--------------------------------------------------------------------------
    | Vector Dimensions
    |--------------------------------------------------------------------------
    |
    | The dimensionality of the embedding vectors. This MUST match:
    | 1. The model's output dimension
    | 2. The vector column definition in migrations
    |
    | Common values:
    | - text-embedding-3-small: 1536
    | - text-embedding-3-large: 3072
    | - text-embedding-ada-002: 1536
    |
    */

    'dimensions' => (int) env('EMBEDDING_DIMENSIONS', 1536),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout_seconds' => (int) env('OPENAI_EMBEDDING_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunking Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for splitting documents into chunks before embedding.
    |
    */

    'chunking' => [
        'max_tokens_per_chunk' => (int) env('CHUNK_MAX_TOKENS', 500),
        'overlap_tokens' => (int) env('CHUNK_OVERLAP_TOKENS', 50),
        'min_chunk_tokens' => (int) env('CHUNK_MIN_TOKENS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Similarity Search
    |--------------------------------------------------------------------------
    |
    | Settings for pgvector similarity search.
    |
    */

    'search' => [
        'max_results' => (int) env('EMBEDDING_SEARCH_MAX_RESULTS', 10),
        'min_similarity' => (float) env('EMBEDDING_SEARCH_MIN_SIMILARITY', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    */

    'processing' => [
        'queue' => env('EMBEDDING_QUEUE', 'embeddings'),
        'batch_size' => (int) env('EMBEDDING_BATCH_SIZE', 20),
    ],

];
