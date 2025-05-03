<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Category Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Group routes under '/categories' prefix and 'categories.' name prefix
// Assumes category management requires authentication and 'manage categories' permission (middleware applied in Controller)
Route::prefix('categories')->name('categories.')->middleware(['auth'])->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit'); // Using Route Model Binding
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update'); // Using Route Model Binding
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy'); // Using Route Model Binding

    // Alternative: Use Route::resource
    // Route::resource('categories', CategoryController::class);
});
