<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/upload', [DashboardController::class, 'upload'])->name('dashboard.upload');

Route::get('/analisis/{document}', [DashboardController::class, 'show'])->name('analysis.show');
Route::get('/analisis/{document}/clausulas', [DashboardController::class, 'showClauses'])->name('analysis.clauses');
