<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/upload', [DashboardController::class, 'upload'])->name('dashboard.upload');
    Route::get('/dashboard/documents/{document}/status', [DashboardController::class, 'documentStatus'])
        ->name('dashboard.document.status');

    Route::get('/analisis/{document}', [DashboardController::class, 'show'])->name('analysis.show');
    Route::get('/analisis/{document}/clausulas', [DashboardController::class, 'showClauses'])->name('analysis.clauses');

    Route::post('/user/dismiss-tutorial', [\App\Http\Controllers\UserController::class, 'dismissTutorial'])
        ->name('user.dismiss-tutorial');
});
