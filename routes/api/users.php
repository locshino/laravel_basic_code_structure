<?php
// routes/api/users.php
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "api" middleware group.
|
*/

// Group routes under '/users' prefix
// Assumes user management requires authentication and 'manage users' permission
Route::prefix('users')->middleware(['auth:sanctum', 'can:manage users'])->group(function () { // Apply middleware to the group
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);

    // Route for assigning roles via API
    Route::put('/{id}/roles', [UserController::class, 'assignRole'])->middleware('can:assign roles'); // Apply specific permission middleware

    // Alternative: Use Route::apiResource
    // Route::apiResource('users', UserController::class);
});
