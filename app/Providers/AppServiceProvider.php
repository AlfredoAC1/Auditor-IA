<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\LLM\LLMAnalysisServiceInterface;
use App\Services\Dashboard\DashboardMetricsService;
use App\Services\Documents\DocumentParsingService;
use App\Services\Documents\DocumentProcessingDispatcher;
use App\Services\Documents\DocumentStorageService;
use App\Services\LLM\LlmAnalysisService;
use App\Services\LLM\LlmPromptBuilder;
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
        $this->app->singleton(ExternalServiceHealthService::class);
        $this->app->singleton(DocumentProcessingDispatcher::class);
        $this->app->singleton(DashboardMetricsService::class);

        // Prompt builder is stateless — share one instance
        $this->app->singleton(LlmPromptBuilder::class);

        // Unified LLM analysis service (resolves provider at call-time, not boot-time)
        $this->app->singleton(LlmAnalysisService::class);

        // Bind the interface to the unified service
        $this->app->bind(LLMAnalysisServiceInterface::class, LlmAnalysisService::class);
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
