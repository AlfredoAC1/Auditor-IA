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
use App\Services\LLM\LlmServiceResolver;
use App\Services\LLM\ProLLMAnalysisService;
use App\Services\LLM\ProLlmPromptBuilder;
use App\Services\System\ExternalServiceHealthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
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

        // Prompt builders (stateless)
        $this->app->singleton(LlmPromptBuilder::class);
        $this->app->singleton(ProLlmPromptBuilder::class);

        // Free tier LLM analysis service (Cerebras GPT-OSS 20B exclusive)
        $this->app->singleton(LlmAnalysisService::class);

        // Pro & Lifetime LLM analysis service (Qwen Cloud DashScope exclusive)
        $this->app->singleton(ProLLMAnalysisService::class);

        // Tenant-aware LLM service resolver
        $this->app->singleton(LlmServiceResolver::class);

        // Dynamically resolve the interface according to the current tenant/user
        $this->app->bind(LLMAnalysisServiceInterface::class, function ($app) {
            /** @var LlmServiceResolver $resolver */
            $resolver = $app->make(LlmServiceResolver::class);

            return $resolver->resolveForUser(\Illuminate\Support\Facades\Auth::user());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strict Eloquent safety in non-production environments
        // Prevents lazy loading, silently discarded mass assignments, and accessing missing attributes.
        Model::shouldBeStrict(! $this->app->environment('production'));

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
