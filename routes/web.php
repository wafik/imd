<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImdController;
use App\Http\Controllers\AskAIController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [AuthenticatedSessionController::class, 'create'])
    ->name('home');
Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // IMD Routes
    Route::resource('imds', ImdController::class)->except(['create', 'edit']);
    Route::get('imds/detail/{imd}', [ImdController::class, 'show'])->name('imds.show');
    Route::get('download/export', [ImdController::class, 'export'])->name('imds.export');

    // Ask AI Routes
    Route::get('ask-ai', [AskAIController::class, 'index'])->name('ask-ai');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
