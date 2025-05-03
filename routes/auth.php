<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Routes for guests (not logged in users)
Route::middleware('guest')->group(function () {
    // Show login form
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    // Handle login form submission
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Optional: Registration routes if you implement them
    // Route::get('register', [RegisteredUserController::class, 'create'])
    //             ->name('register');
    // Route::post('register', [RegisteredUserController::class, 'store']);

    // Optional: Password reset routes
    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //             ->name('password.request');
    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //             ->name('password.email');
    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    //             ->name('password.reset');
    // Route::post('reset-password', [NewPasswordController::class, 'store'])
    //             ->name('password.store');
});

// Routes for authenticated users
Route::middleware('auth')->group(function () {
    // Handle logout request
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');

    // Optional: Email verification routes
    // Route::get('verify-email', EmailVerificationPromptController::class)
    //             ->name('verification.notice');
    // Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    //             ->middleware(['signed', 'throttle:6,1'])
    //             ->name('verification.verify');
    // Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    //             ->middleware('throttle:6,1')
    //             ->name('verification.send');

    // Optional: Password confirmation routes
    // Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
    //             ->name('password.confirm');
    // Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
});
