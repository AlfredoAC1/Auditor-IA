<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminAnalysisController;
use App\Http\Controllers\Admin\AdminBugTicketController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrganizationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AncillaryAnalysisController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BugTicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoPreviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/terminos-y-condiciones', function () {
    return view('terms');
})->name('terms');

Route::prefix('demo')->name('demo.')->group(function (): void {
    Route::get('/', [DemoPreviewController::class, 'index'])->name('dashboard');
    Route::get('/analisis/{slug}', [DemoPreviewController::class, 'show'])->name('analysis.show');
    Route::get('/clausulas/{slug}', [DemoPreviewController::class, 'showClauses'])->name('analysis.clauses');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/model', [DashboardController::class, 'setModel'])->name('dashboard.model');
    Route::post('/dashboard/provider', [DashboardController::class, 'setProvider'])->name('dashboard.provider');
    Route::post('/dashboard/upload', [DashboardController::class, 'upload'])->name('dashboard.upload');
    Route::get('/dashboard/documents/{document}/status', [DashboardController::class, 'documentStatus'])
        ->name('dashboard.document.status');
    Route::delete('/dashboard/documents/{document}', [DashboardController::class, 'destroy'])
        ->name('dashboard.documents.destroy');

    Route::get('/analisis/{document}', [DashboardController::class, 'show'])->name('analysis.show');
    Route::get('/analisis/{document}/clausulas', [DashboardController::class, 'showClauses'])->name('analysis.clauses');
    Route::post('/analisis/{analysis}/ancillary', [AncillaryAnalysisController::class, 'upload'])
        ->name('analysis.ancillary.upload');
    Route::get('/analisis/{analysis}/ancillary/{ancillaryDocument}/status', [AncillaryAnalysisController::class, 'status'])
        ->name('analysis.ancillary.status');
    Route::get('/analisis/{document}/contextual/{analysis}', [AncillaryAnalysisController::class, 'showContextual'])
        ->name('analysis.contextual.show');

    Route::post('/user/dismiss-tutorial', [UserController::class, 'dismissTutorial'])
        ->name('user.dismiss-tutorial');
    Route::post('/user/dismiss-ancillary-tutorial', [UserController::class, 'dismissAncillaryTutorial'])
        ->name('user.dismiss-ancillary-tutorial');

    // Account settings
    Route::get('/configuracion', [UserController::class, 'showSettings'])->name('settings');
    Route::post('/configuracion/profile', [UserController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/configuracion/avatar', [UserController::class, 'updateAvatar'])->name('settings.avatar');
    Route::get('/configuracion/avatar/{user}', [UserController::class, 'serveAvatar'])->name('settings.avatar.serve');
    Route::post('/configuracion/password', [UserController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/configuracion/account', [UserController::class, 'deleteAccount'])->name('settings.delete');

    // Bug ticket submission
    Route::post('/tickets', [BugTicketController::class, 'store'])->name('tickets.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Organizations management
    Route::get('/organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/organizations/{organization}/toggle', [AdminOrganizationController::class, 'toggleStatus'])->name('organizations.toggle');

    // Analysis auditing
    Route::get('/analyses', [AdminAnalysisController::class, 'index'])->name('analyses.index');
    Route::get('/analyses/{analysis}', [AdminAnalysisController::class, 'show'])->name('analyses.show');

    // Bug tickets
    Route::get('/tickets', [AdminBugTicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{ticket}/resolve', [AdminBugTicketController::class, 'resolve'])->name('tickets.resolve');
});
