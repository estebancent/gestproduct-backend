<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use Illuminate\Support\Facades\Artisan;

// 🔓 Pública 
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); 
// 5 intentos por minuto máximo.
// 🔐 Protegidas
Route::middleware('auth:sanctum')->group(function () {

    // Usuario logueado
    Route::get('/me', function (Request $request) {
    return $request->user()->load('role');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD automático
    Route::apiResource('users', UserController::class);

    // Rutas extra
    Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);
    Route::post('/users/{id}/restore', [UserController::class, 'restore']);
});

// Resources estándar
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('variants', ProductVariantController::class)->except(['index', 'show']);
    
    // Ruta para ajuste rápido de stock (Inventario físico)
    Route::patch('variants/{id}/stock', [ProductVariantController::class, 'updateStock']);
    // Rutas Custom para SoftDelete y Activación
    Route::prefix('brands')->group(function () {
        Route::post('{id}/restore', [BrandController::class, 'restore']);
        Route::patch('{id}/toggle', [BrandController::class, 'toggleActive']);
    });

    Route::prefix('categories')->group(function () {
        Route::post('{id}/restore', [CategoryController::class, 'restore']);
        Route::patch('{id}/toggle', [CategoryController::class, 'toggleActive']);
    });

    Route::prefix('suppliers')->group(function () {
        Route::post('{id}/restore', [SupplierController::class, 'restore']);
        Route::patch('{id}/toggle', [SupplierController::class, 'toggleActive']);
    });
    // routes/api.php
Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return "Caché limpia";
});