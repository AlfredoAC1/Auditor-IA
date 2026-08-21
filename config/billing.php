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
    | AI credits replace the old max_analyses_per_month and max_tokens_per_month.
    |
    | credit_refresh_interval:
    |   - 'weekly'  → Free tier (resets 7 days from last usage)
    |   - 'monthly' → Pro/Lifetime (calendar month cycle)
    |
    | allowed_model_tiers:
    |   - Free users: only 'lightweight' (model is fixed, can choose analysis type)
    |   - Pro/Lifetime: all tiers (can choose between AI models, providers hidden)
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
            'max_storage_bytes' => 104857600, // 100 MB
            'ai_credits_quota' => 25,
            'credit_refresh_interval' => 'weekly',
            'allowed_model_tiers' => ['lightweight'],
            'highlights' => [
                'Resumen y detección de riesgos',
                'Análisis según legislación chilena',
                '25 créditos IA semanales',
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
            'max_storage_bytes' => 5368709120, // 5 GB
            'ai_credits_quota' => 500,
            'credit_refresh_interval' => 'monthly',
            'allowed_model_tiers' => ['lightweight', 'standard', 'advanced', 'premium'],
            'popular' => true,
            'highlights' => [
                'Todos los tipos de análisis',
                'Selección de modelo IA',
                '500 créditos IA mensuales',
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
            'max_storage_bytes' => null,
            'ai_credits_quota' => null, // Unlimited
            'credit_refresh_interval' => 'monthly',
            'allowed_model_tiers' => ['lightweight', 'standard', 'advanced', 'premium'],
            'highlights' => [
                'Todas las funciones actuales y futuras',
                'Créditos IA ilimitados',
                'Selección de modelo IA',
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
