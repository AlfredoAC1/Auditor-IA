<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    /**
     * Test that the custom 419 error view renders properly.
     */
    public function test_custom_419_error_view_renders(): void
    {
        $view = $this->view('errors.419');

        $view->assertSee('Error 419 — Sesión Expirada / Token CSRF Inválido');
        $view->assertSee('419');
        $view->assertSee('La sesión ha caducado por inactividad');
        $view->assertSee('Tiempo de Sesión Agotado');
        $view->assertSee('Recargar Página');
        $view->assertSee('Iniciar Sesión');
    }

    /**
     * Test that the custom 404 error view renders properly.
     */
    public function test_custom_404_error_view_renders(): void
    {
        $view = $this->view('errors.404');

        $view->assertSee('Error 404 — Cláusula No Encontrada');
        $view->assertSee('404');
        $view->assertSee('Parece que esta página se ha traspapelado');
        $view->assertSee('Volver al Inicio');
    }
}
