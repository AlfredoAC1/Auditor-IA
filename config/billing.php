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
            'ai_credits_quota' => 100,
            'credit_refresh_interval' => 'weekly',
            'allowed_model_tiers' => ['lightweight'],
            'highlights' => [
                '100 créditos IA semanales (~2 a 3 análisis)',
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
            'price_monthly_clp' => 19990,
            'price_yearly_clp' => 199900,
            'price_lifetime_clp' => null,
            'max_documents_per_month' => 50,
            'max_storage_bytes' => 5368709120, // 5 GB
            'ai_credits_quota' => 1000,
            'credit_refresh_interval' => 'monthly',
            'allowed_model_tiers' => ['lightweight', 'standard', 'advanced', 'premium'],
            'popular' => true,
            'highlights' => [
                '1.000 créditos IA mensuales',
                'Todos los tipos de análisis',
                'Selección de modelo IA',
                'Prioridad en procesamiento',
                'Exportación de informes',
                'Soporte prioritario',
            ],
        ],

        'lifetime' => [
            'name' => 'Lifetime',
            'description' => 'Acceso Pro de por vida, pago único sin mensualidades',
            'is_free' => false,
            'billing_type' => 'lifetime',
            'price_monthly_clp' => null,
            'price_yearly_clp' => null,
            'price_lifetime_clp' => 199990,
            'limited_offer' => true,
            'max_documents_per_month' => 50,
            'max_storage_bytes' => 5368709120, // 5 GB
            'ai_credits_quota' => 1000,
            'credit_refresh_interval' => 'monthly',
            'allowed_model_tiers' => ['lightweight', 'standard', 'advanced', 'premium'],
            'highlights' => [
                '1.000 créditos IA / mes de por vida',
                'Todas las funciones del plan Pro',
                'Selección de modelo IA',
                'Pago único, sin suscripción recurrente',
                'Soporte prioritario de por vida',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | AI Credits Economy Configuration
    |--------------------------------------------------------------------------
    |
    | Formula: ((Input Tokens + Output Tokens) / tokens_per_credit) * Model Weight
    |
    */

    'credits' => [
        'tokens_per_credit' => (int) env('AI_CREDITS_TOKENS_PER_CREDIT', 175),
        'min_charge' => (int) env('AI_CREDITS_MIN_CHARGE', 1),
        'rounding' => 'ceil',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default plan slug for organizations without subscription
    |--------------------------------------------------------------------------
    */

    'default_plan' => 'free',

];
