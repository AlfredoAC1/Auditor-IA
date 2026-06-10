<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for legal document uploads including size limits,
    | allowed MIME types, and storage settings. All values can be
    | overridden via environment variables.
    |
    */

    'upload' => [
        'max_size_kb' => (int) env('DOCUMENT_MAX_SIZE_KB', 51200), // 50MB default
        'allowed_mimes' => [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'allowed_extensions' => ['pdf', 'docx'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Disk and path configuration for document storage. Documents are
    | always stored in a private disk with tenant-isolated paths.
    |
    */

    'storage' => [
        'disk' => env('DOCUMENT_STORAGE_DISK', 'local'),
        'base_path' => 'documents',
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Queue and retry settings for document processing jobs.
    |
    */

    'processing' => [
        'queue' => env('DOCUMENT_PROCESSING_QUEUE', 'documents'),
        'max_retries' => (int) env('DOCUMENT_PROCESSING_MAX_RETRIES', 3),
        'retry_backoff_seconds' => [30, 120, 600],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default and maximum pagination limits for document listings.
    |
    */

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Unstructured API Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the Unstructured document parsing service.
    | Used to convert PDF/DOCX into structured JSON for LLM analysis.
    |
    | Local: docker run -p 8000:8000 quay.io/unstructured-io/unstructured-api
    | Cloud: https://api.unstructured.io/general/v0/general
    |
    */

    'unstructured' => [
        'api_url' => env('UNSTRUCTURED_API_URL', 'http://localhost:8000/general/v0/general'),
        'api_key' => env('UNSTRUCTURED_API_KEY', ''),
        'timeout_seconds' => (int) env('UNSTRUCTURED_TIMEOUT', 120),
    ],

];
