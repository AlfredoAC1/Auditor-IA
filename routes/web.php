<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
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

    Route::post('/user/dismiss-tutorial', [UserController::class, 'dismissTutorial'])
        ->name('user.dismiss-tutorial');

    // Account settings
    Route::get('/configuracion', [UserController::class, 'showSettings'])->name('settings');
    Route::post('/configuracion/profile', [UserController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/configuracion/avatar', [UserController::class, 'updateAvatar'])->name('settings.avatar');
    Route::get('/configuracion/avatar/{user}', [UserController::class, 'serveAvatar'])->name('settings.avatar.serve');
    Route::post('/configuracion/password', [UserController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/configuracion/account', [UserController::class, 'deleteAccount'])->name('settings.delete');

    // Bug ticket submission
    Route::post('/tickets', [\App\Http\Controllers\BugTicketController::class, 'store'])->name('tickets.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [\App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');

    // Organizations management
    Route::get('/organizations', [\App\Http\Controllers\Admin\AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/organizations/{organization}/toggle', [\App\Http\Controllers\Admin\AdminOrganizationController::class, 'toggleStatus'])->name('organizations.toggle');

    // Analysis auditing
    Route::get('/analyses', [\App\Http\Controllers\Admin\AdminAnalysisController::class, 'index'])->name('analyses.index');
    Route::get('/analyses/{analysis}', [\App\Http\Controllers\Admin\AdminAnalysisController::class, 'show'])->name('analyses.show');

    // Bug tickets
    Route::get('/tickets', [\App\Http\Controllers\Admin\AdminBugTicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{ticket}/resolve', [\App\Http\Controllers\Admin\AdminBugTicketController::class, 'resolve'])->name('tickets.resolve');
});
