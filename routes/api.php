<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

// 🔓 Pública
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Protegidas
Route::middleware('auth:sanctum')->group(function () {

    // Usuario logueado
    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD automático
    Route::apiResource('users', UserController::class);

    // Rutas extra
    Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
});