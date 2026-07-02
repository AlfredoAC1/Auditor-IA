<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TermsAndConditionsTest extends TestCase
{
    /**
     * Test that the terms and conditions route returns a successful response.
     */
    public function test_terms_and_conditions_page_is_accessible(): void
    {
        $response = $this->get('/terminos-y-condiciones');

        $response->assertStatus(200);
        $response->assertSee('Términos y Condiciones de Uso');
        $response->assertSee('Exclusión de Asesoría Legal');
        $response->assertSee('responsabilidad');
        $response->assertSee('CÓDIGO CIVIL');
        $response->assertSee('Ley N° 19.496');
        $response->assertSee('Ley N° 19.628');
    }

    /**
     * Test that the landing page footer contains the link to terms and conditions.
     */
    public function test_landing_page_has_terms_and_conditions_link(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('terms'));
        $response->assertSee('Términos y Condiciones');
    }
}
