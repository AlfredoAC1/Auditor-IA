<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\DocumentController;
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
    });
