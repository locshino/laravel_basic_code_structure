<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Group routes under '/users' prefix and 'users.' name prefix
// Assumes user management requires authentication and 'manage users' permission (middleware applied in Controller)
Route::prefix('users')->name('users.')->middleware(['auth'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/create', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}', [UserController::class, 'show'])->name('show'); // Using Route Model Binding
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit'); // Using Route Model Binding
    Route::put('/{user}', [UserController::class, 'update'])->name('update'); // Using Route Model Binding
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy'); // Using Route Model Binding

    // Routes for role management (requires 'assign roles' permission)
    Route::get('/{user}/roles/assign', [UserController::class, 'showAssignRoleForm'])->name('assign_role.form'); // Added 'assign' to avoid conflict with {user}
    Route::put('/{user}/roles', [UserController::class, 'assignRole'])->name('assign_role'); // Assuming a single role assignment form

    // Alternative: Use Route::resource
    // Route::resource('users', UserController::class);
});
