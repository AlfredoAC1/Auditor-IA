<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LandingPricingTest extends TestCase
{
    /**
     * Test that landing page renders updated prices, AI credits explanations, and limited offer disclaimer.
     */
    public function test_landing_page_renders_updated_prices_and_ai_credits_card(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();

        // Updated prices
        $response->assertSee('$19.990'); // Pro monthly price
        $response->assertSee('$199.990'); // Pro yearly or Lifetime price

        // Oferta Limitada disclaimer
        $response->assertSee('Oferta Limitada');

        // AI Credits logic on plan cards
        $response->assertSee('25 créditos IA / semana');
        $response->assertSee('500 créditos IA / mes');
        $response->assertSee('Créditos IA ilimitados');

        // AI Credits explanation card
        $response->assertSee('¿Cómo funcionan los Créditos IA en Auditor-IA?');
        $response->assertSee('Modelo Básico');
        $response->assertSee('Modelo Estándar');
        $response->assertSee('Modelo Avanzado');
        $response->assertSee('Modelo Premium');
        $response->assertSee('Renovación automática:');
    }
}
