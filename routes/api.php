<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

// ── Rutas PÚBLICAS (no requieren token) ─────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',      [AuthController::class, 'login']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
});

// ── Rutas PROTEGIDAS (requieren Bearer Token de Sanctum) ─────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // Servicios - CRUD completo
    Route::apiResource('services', ServiceController::class);
});
