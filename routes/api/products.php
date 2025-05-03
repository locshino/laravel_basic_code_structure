<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group.
|
*/

// Group routes under '/products' prefix
Route::prefix('products')->group(function () {
    // Public access to index and show
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']); // Use {id} for API

    // Routes requiring authentication and specific permissions
    // Apply authentication middleware (e.g., auth:sanctum) and permission middleware (e.g., can)
    Route::middleware(['auth:sanctum'])->group(function () { // Example using Sanctum
        Route::post('/', [ProductController::class, 'store'])->middleware('can:create products');
        Route::put('/{id}', [ProductController::class, 'update'])->middleware('can:update products');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('can:delete products');
    });

    // Alternative: Use Route::apiResource for standard API CRUD routes
    // Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    // Route::get('/products', [ProductController::class, 'index']);
    // Route::get('/products/{id}', [ProductController::class, 'show']);
});
