<?php

declare(strict_types=1);

use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {
    // Public routes (no authentication required)
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1'); // 10 requests per minute
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1'); // 10 requests per minute

    // Public product routes (accessible to all)
    Route::apiResource('/products', ProductController::class)->only(['index', 'show']);

    // Protected routes (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Admin product routes (requires admin role)
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::apiResource('/products', AdminProductController::class);
            Route::apiResource('/users', AdminUserController::class);
        });
    });
});

