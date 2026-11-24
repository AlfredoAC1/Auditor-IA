<?php

declare(strict_types=1);

namespace Tests\Feature\Demo;

use App\Support\Demo\StaticDemoDataset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class StaticDemoExportTest extends TestCase
{
    private string $tempOutputDir = 'vercel-dist-test';

    protected function tearDown(): void
    {
        $fullPath = base_path($this->tempOutputDir);
        if (File::isDirectory($fullPath)) {
            File::deleteDirectory($fullPath);
        }

        parent::tearDown();
    }

    public function test_demo_preview_routes_render_without_database_or_http_calls(): void
    {
        Http::preventStrayRequests();
        DB::enableQueryLog();
        DB::flushQueryLog();

        $dashboardResponse = $this->get(route('demo.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Dashboard Pro');
        $dashboardResponse->assertSee('Vista Demostrativa Estática');

        foreach (StaticDemoDataset::documents() as $doc) {
            $detailResponse = $this->get(route('demo.analysis.show', $doc['slug']));
            $detailResponse->assertOk();
            $detailResponse->assertSee($doc['original_filename']);

            $clausesResponse = $this->get(route('demo.analysis.clauses', $doc['slug']));
            $clausesResponse->assertOk();
            $clausesResponse->assertSee('Desglose de Cláusulas');
        }

        $missingResponse = $this->get(route('demo.analysis.show', 'documento-inexistente'));
        $missingResponse->assertNotFound();

        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_dev_landing_still_points_to_login_and_register(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee(route('login'), false);
        $response->assertSee(route('register'), false);
    }

    public function test_static_demo_export_builds_pure_html_with_zero_api_or_localhost_references(): void
    {
        $this->artisan('demo:build-static', ['--output' => $this->tempOutputDir])
            ->assertExitCode(0);

        $out = base_path($this->tempOutputDir);

        $expectedFiles = [
            'index.html',
            'terminos-y-condiciones.html',
            '404.html',
            'demo/index.html',
            'build/manifest.json',
            'images/logo.png',
            'images/text-logo.png',
        ];

        foreach (StaticDemoDataset::documents() as $doc) {
            $expectedFiles[] = "demo/analisis/{$doc['slug']}.html";
            $expectedFiles[] = "demo/clausulas/{$doc['slug']}.html";
        }

        foreach ($expectedFiles as $relativePath) {
            $this->assertFileExists($out.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        }

        $landingHtml = File::get($out.DIRECTORY_SEPARATOR.'index.html');
        $this->assertStringContainsString('href="/demo"', $landingHtml);
        $this->assertStringNotContainsString('href="/login"', $landingHtml);
        $this->assertStringNotContainsString('href="/register"', $landingHtml);

        $forbiddenStrings = [
            '127.0.0.1',
            'localhost',
            'demo-static.invalid',
            'api.groq.com',
            'api.cerebras.ai',
            'dashscope-intl.aliyuncs.com',
            'integrate.api.nvidia.com',
            'api.openai.com',
            'unstructuredapp.io',
        ];

        $htmlFiles = collect(File::allFiles($out))
            ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'html');

        $this->assertGreaterThanOrEqual(8, $htmlFiles->count());

        foreach ($htmlFiles as $htmlFile) {
            $content = File::get($htmlFile->getPathname());
            foreach ($forbiddenStrings as $forbidden) {
                $this->assertStringNotContainsString(
                    $forbidden,
                    $content,
                    "Forbidden reference [{$forbidden}] found in [{$htmlFile->getFilename()}]",
                );
            }
        }
    }
}
