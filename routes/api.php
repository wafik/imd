<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImdController;
use App\Http\Controllers\AskAIController;

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

// IMD Data routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('imd', ImdController::class);
});

// Ask AI routes (protected with web auth for session)
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('ask-ai/question', [AskAIController::class, 'askQuestion'])->name('api.ask-ai.question');
    Route::post('ask-ai/execute-query', [AskAIController::class, 'executeQueryEndpoint'])->name('api.ask-ai.execute-query');
    Route::get('ask-ai/samples', [AskAIController::class, 'getSampleQuestions'])->name('api.ask-ai.samples');
});

// User route (for compatibility)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
