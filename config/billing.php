<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
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
    | NULL values mean unlimited.
    | price_monthly_clp / price_yearly_clp used for recurring plans.
    | price_lifetime_clp used for one-time Lifetime plan.
    |
    */

    'plans' => [

        'free' => [
            'name' => 'Free',
            'description' => 'Ideal para probar la plataforma sin compromiso',
            'is_free' => true,
            'billing_type' => 'free',
            'price_monthly_clp' => 0,
            'price_yearly_clp' => 0,
            'price_lifetime_clp' => null,
            'max_documents_per_month' => 3,
            'max_analyses_per_month' => 5,
            'max_storage_bytes' => 104857600, // 100 MB
            'max_tokens_per_month' => 50000,
            'highlights' => [
                'Resumen y detección de riesgos',
                'Análisis según legislación chilena',
                'Soporte por email',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'description' => 'Para profesionales que revisan contratos con frecuencia',
            'is_free' => false,
            'billing_type' => 'recurring',
            'price_monthly_clp' => 14990,
            'price_yearly_clp' => 149900,
            'price_lifetime_clp' => null,
            'max_documents_per_month' => 50,
            'max_analyses_per_month' => 200,
            'max_storage_bytes' => 5368709120, // 5 GB
            'max_tokens_per_month' => 2000000,
            'popular' => true,
            'highlights' => [
                'Todos los tipos de análisis',
                'Prioridad en procesamiento',
                'Exportación de informes',
                'Soporte prioritario',
            ],
        ],

        'lifetime' => [
            'name' => 'Lifetime',
            'description' => 'Acceso completo de por vida, pago único',
            'is_free' => false,
            'billing_type' => 'lifetime',
            'price_monthly_clp' => null,
            'price_yearly_clp' => null,
            'price_lifetime_clp' => 399900,
            'max_documents_per_month' => null,
            'max_analyses_per_month' => null,
            'max_storage_bytes' => null,
            'max_tokens_per_month' => null,
            'highlights' => [
                'Todas las funciones actuales y futuras',
                'Sin renovaciones ni sorpresas',
                'Soporte prioritario de por vida',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default plan slug for organizations without subscription
    |--------------------------------------------------------------------------
    */

    'default_plan' => 'free',

];
