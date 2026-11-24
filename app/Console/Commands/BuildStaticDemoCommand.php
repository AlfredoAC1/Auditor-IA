<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Demo\StaticDemoDataset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use RuntimeException;

/**
 * Exports a purely static HTML/CSS/JS bundle for the Vercel demonstrative
 * production environment with zero .env, database, or external API dependencies.
 */
class BuildStaticDemoCommand extends Command
{
    private const PLACEHOLDER_ORIGIN = 'http://demo-static.invalid';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:build-static
                            {--output=vercel-dist : Output directory relative to the project root}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build a purely static demonstrative HTML bundle for Vercel without DB or API calls';

    public function handle(): int
    {
        $manifestPath = public_path('build/manifest.json');
        if (! File::exists($manifestPath)) {
            $this->error('Missing public/build/manifest.json. Run "npm run build" before exporting the static demo.');

            return self::FAILURE;
        }

        $outputOption = trim((string) $this->option('output'), "/\\ \t\n\r\0\x0B");
        if ($outputOption === '' || str_contains($outputOption, '..')) {
            $this->error('Invalid output directory.');

            return self::FAILURE;
        }

        $outputDir = base_path($outputOption);

        $this->prepareOutputDirectory($outputDir);
        $this->copyStaticAssets($outputDir);

        $generatedPages = $this->renderStaticPages($outputDir);

        $this->info("Static Vercel demo exported to [{$outputOption}] ({$generatedPages} HTML pages, 0 DB queries, 0 HTTP calls).");

        return self::SUCCESS;
    }

    private function prepareOutputDirectory(string $outputDir): void
    {
        if (File::isDirectory($outputDir)) {
            File::deleteDirectory($outputDir);
        }

        File::makeDirectory($outputDir, 0755, true);
    }

    private function copyStaticAssets(string $outputDir): void
    {
        File::copyDirectory(public_path('build'), $outputDir.DIRECTORY_SEPARATOR.'build');

        if (File::isDirectory(public_path('images'))) {
            File::copyDirectory(public_path('images'), $outputDir.DIRECTORY_SEPARATOR.'images');
        }

        foreach (['favicon.ico', 'robots.txt'] as $rootAsset) {
            $source = public_path($rootAsset);
            if (File::exists($source)) {
                File::copy($source, $outputDir.DIRECTORY_SEPARATOR.$rootAsset);
            }
        }
    }

    private function renderStaticPages(string $outputDir): int
    {
        $previousAppUrl = config('app.url');
        $count = 0;

        Http::preventStrayRequests();
        DB::enableQueryLog();
        DB::flushQueryLog();

        URL::forceRootUrl(self::PLACEHOLDER_ORIGIN);
        config(['app.url' => self::PLACEHOLDER_ORIGIN]);
        View::share('isStaticDemo', true);

        try {
            $pages = [
                'index.html' => view('landing')->render(),
                'terminos-y-condiciones.html' => view('terms')->render(),
                '404.html' => view('errors.404')->render(),
                'demo/index.html' => view('demo.dashboard-pro', [
                    'user' => StaticDemoDataset::user(),
                    'credits' => StaticDemoDataset::credits(),
                    'metrics' => StaticDemoDataset::metrics(),
                    'analysisTypeCounts' => StaticDemoDataset::analysisTypeCounts(),
                    'aiModels' => StaticDemoDataset::aiModels(),
                    'documents' => StaticDemoDataset::documents(),
                ])->render(),
            ];

            foreach (StaticDemoDataset::documents() as $document) {
                $slug = (string) $document['slug'];
                $pages["demo/analisis/{$slug}.html"] = view('demo.analysis-detail', [
                    'document' => $document,
                ])->render();

                $pages["demo/clausulas/{$slug}.html"] = view('demo.analysis-clauses', [
                    'document' => $document,
                ])->render();
            }

            if (count(DB::getQueryLog()) > 0) {
                throw new RuntimeException('Static demo export executed database queries, which violates zero-DB isolation.');
            }

            foreach ($pages as $relativePath => $html) {
                $normalizedHtml = $this->normalizeStaticUrls($html);
                $targetPath = $outputDir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                File::ensureDirectoryExists(dirname($targetPath));
                File::put($targetPath, $normalizedHtml);
                $count++;
            }
        } finally {
            View::share('isStaticDemo', false);
            URL::forceRootUrl(null);
            config(['app.url' => $previousAppUrl]);
            DB::disableQueryLog();
        }

        return $count;
    }

    private function normalizeStaticUrls(string $html): string
    {
        $html = str_replace(self::PLACEHOLDER_ORIGIN.'/', '/', $html);

        return str_replace(self::PLACEHOLDER_ORIGIN, '/', $html);
    }
}
