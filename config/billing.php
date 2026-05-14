<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | Stripe API keys for payment processing.
    | Use test keys during development.
    |
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => 'clp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Plans
    |--------------------------------------------------------------------------
    |
    | Seed data for available plans. These are used by the PlanSeeder
    | to populate the plans table. Adjust limits as needed.
    |
    | NULL values mean unlimited.
    |
    */

    'plans' => [

        'free' => [
            'name' => 'Gratis',
            'description' => 'Para probar la plataforma',
            'price_clp' => 0,
            'is_free' => true,
            'max_documents_per_month' => 3,
            'max_analyses_per_month' => 5,
            'max_storage_bytes' => 104857600, // 100 MB
            'max_tokens_per_month' => 50000,
        ],

        'basic' => [
            'name' => 'Básico',
            'description' => 'Para uso personal o pequeñas empresas',
            'price_clp' => 9990,
            'is_free' => false,
            'max_documents_per_month' => 20,
            'max_analyses_per_month' => 50,
            'max_storage_bytes' => 1073741824, // 1 GB
            'max_tokens_per_month' => 500000,
        ],

        'professional' => [
            'name' => 'Profesional',
            'description' => 'Para profesionales y empresas medianas',
            'price_clp' => 29990,
            'is_free' => false,
            'max_documents_per_month' => 100,
            'max_analyses_per_month' => null, // Unlimited
            'max_storage_bytes' => 10737418240, // 10 GB
            'max_tokens_per_month' => null, // Unlimited
        ],

        'enterprise' => [
            'name' => 'Empresa',
            'description' => 'Para grandes organizaciones con necesidades especiales',
            'price_clp' => null, // Custom pricing
            'is_free' => false,
            'max_documents_per_month' => null, // Unlimited
            'max_analyses_per_month' => null, // Unlimited
            'max_storage_bytes' => null, // Unlimited
            'max_tokens_per_month' => null, // Unlimited
        ],

    ],

];
