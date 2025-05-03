<?php
// routes/web/dashboard.php
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Route for the dashboard page
// This route requires authentication (middleware applied in DashboardController constructor)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
