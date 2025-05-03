<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Group routes under '/products' prefix and 'products.' name prefix
Route::prefix('products')->name('products.')->group(function () {
    // Guest/Public access to index and show (Policies/Gates will handle visibility)
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show'); // Using Route Model Binding

    // Routes requiring authentication and specific permissions (Manager/Admin)
    Route::middleware(['auth'])->group(function () {
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit'); // Using Route Model Binding
        Route::post('/{product}', [ProductController::class, 'update'])->name('update'); // Using Route Model Binding
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy'); // Using Route Model Binding
    });

    // Alternative: Use Route::resource for standard CRUD routes
    // Route::resource('products', ProductController::class)->except(['index', 'show']);
    // Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

});
