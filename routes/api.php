<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImdController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AskAIController;
use App\Http\Controllers\AskAIController as WebAskAIController;

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/upload-avatar', [AuthController::class, 'uploadAvatar']);
    });
});

// Dashboard routes (protected)
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/charts', [DashboardController::class, 'charts']);
});

// Ask AI routes (protected)
Route::middleware('auth:sanctum')->prefix('ask-ai')->group(function () {
    Route::post('/question', [AskAIController::class, 'askQuestion']);
    Route::post('/execute-query', [AskAIController::class, 'executeQueryEndpoint']);
    Route::get('/samples', [AskAIController::class, 'getSampleQuestions']);
    Route::get('/schema', [AskAIController::class, 'getSchema']);
});

// IMD Data routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('imds', ImdController::class);
});

// Legacy Ask AI routes (protected with web auth for session) - for web interface
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('ask-ai/question', [WebAskAIController::class, 'askQuestion'])->name('api.ask-ai.question');
    Route::post('ask-ai/execute-query', [WebAskAIController::class, 'executeQueryEndpoint'])->name('api.ask-ai.execute-query');
    Route::get('ask-ai/samples', [WebAskAIController::class, 'getSampleQuestions'])->name('api.ask-ai.samples');
});

// User route (for compatibility)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
