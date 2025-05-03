<?php

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group.
|
*/

// Group routes under '/categories' prefix
// Assumes category management requires authentication and 'manage categories' permission
Route::prefix('categories')->middleware(['auth:sanctum', 'can:manage categories'])->group(function () { // Apply middleware to the group
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);

    // Alternative: Use Route::apiResource
    // Route::apiResource('categories', CategoryController::class);
});
