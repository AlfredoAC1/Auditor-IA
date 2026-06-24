<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Documents\DocumentParsingService;
use App\Services\Documents\DocumentStorageService;
use App\Services\LLM\GroqAnalysisService;
use App\Services\System\ExternalServiceHealthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DocumentStorageService::class);
        $this->app->singleton(DocumentParsingService::class);
        $this->app->singleton(GroqAnalysisService::class);
        $this->app->singleton(ExternalServiceHealthService::class);
        $this->app->singleton(\App\Services\Documents\DocumentProcessingDispatcher::class);
        $this->app->singleton(\App\Services\Dashboard\DashboardMetricsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
