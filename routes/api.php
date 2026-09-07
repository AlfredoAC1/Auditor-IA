<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AiModelController;
use App\Http\Controllers\Api\V1\DocumentAnalysisController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\DocumentParsingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes are versioned and require authentication via Sanctum.
| Rate limiting is applied per-route group.
|
*/

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function (): void {

        // Document management
        Route::apiResource('documents', DocumentController::class)
            ->only(['index', 'store', 'show']);

        // Document parsing (Unstructured)
        Route::post('documents/{document}/parse', [DocumentParsingController::class, 'parse']);
        Route::get('documents/{document}/parsed', [DocumentParsingController::class, 'show']);

        // AI models & credits
        Route::get('ai-models', [AiModelController::class, 'index']);

        // Document analysis (LLM)
        Route::post('documents/{document}/analyze', [DocumentAnalysisController::class, 'analyze']);
        Route::get('documents/{document}/analyses', [DocumentAnalysisController::class, 'index']);
        Route::get('documents/{document}/analyses/{analysis}', [DocumentAnalysisController::class, 'show']);

        // Supporting documents — attach additional context to an existing analysis
        Route::post('analyses/{analysis}/supporting-documents', [DocumentAnalysisController::class, 'attachDocument']);
        Route::get('analyses/{analysis}/supporting-documents', [DocumentAnalysisController::class, 'listSupportingDocuments']);
        Route::delete('analyses/{analysis}/supporting-documents/{document}', [DocumentAnalysisController::class, 'detachDocument']);

        // Contextual re-analysis — analyse with all supporting document context
        Route::post('analyses/{analysis}/analyze-with-context', [DocumentAnalysisController::class, 'analyzeWithContext']);
    });
