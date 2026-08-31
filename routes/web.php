<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/terminos-y-condiciones', function () {
    return view('terms');
})->name('terms');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/provider', [DashboardController::class, 'setProvider'])->name('dashboard.provider');
    Route::post('/dashboard/upload', [DashboardController::class, 'upload'])->name('dashboard.upload');
    Route::get('/dashboard/documents/{document}/status', [DashboardController::class, 'documentStatus'])
        ->name('dashboard.document.status');
    Route::delete('/dashboard/documents/{document}', [DashboardController::class, 'destroy'])
        ->name('dashboard.documents.destroy');

    Route::get('/analisis/{document}', [DashboardController::class, 'show'])->name('analysis.show');
    Route::get('/analisis/{document}/clausulas', [DashboardController::class, 'showClauses'])->name('analysis.clauses');

    Route::post('/user/dismiss-tutorial', [\App\Http\Controllers\UserController::class, 'dismissTutorial'])
        ->name('user.dismiss-tutorial');

    // Account settings
    Route::get('/configuracion', [\App\Http\Controllers\UserController::class, 'showSettings'])->name('settings');
    Route::post('/configuracion/profile', [\App\Http\Controllers\UserController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/configuracion/avatar', [\App\Http\Controllers\UserController::class, 'updateAvatar'])->name('settings.avatar');
    Route::get('/configuracion/avatar/{user}', [\App\Http\Controllers\UserController::class, 'serveAvatar'])->name('settings.avatar.serve');
    Route::post('/configuracion/password', [\App\Http\Controllers\UserController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/configuracion/account', [\App\Http\Controllers\UserController::class, 'deleteAccount'])->name('settings.delete');
});
