<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller for handling user authentication sessions (login/logout).
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Return the login view
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request The validated login request.
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Attempt to authenticate the user using the LoginRequest's logic
        /** @var LoginRequest|\Illuminate\Http\Request $request */
        $request->authenticate();

        // Regenerate the session ID to prevent session fixation attacks
        $request->session()->regenerate();

        // Redirect the user to their intended location or the default home page
        // The intended() helper redirects to the URL the user was trying to access before being redirected to login
        return redirect()->intended(RouteServiceProvider::HOME); // Assuming RouteServiceProvider::HOME is defined

        // If you don't have RouteServiceProvider::HOME defined, you can redirect to a specific route:
        // return redirect()->route('dashboard'); // Redirect to a dashboard route
    }

    /**
     * Destroy an authenticated session (log the user out).
     *
     * @param Request $request The incoming request.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log out the user from the 'web' guard
        Auth::guard('web')->logout();

        // Invalidate the user's session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Redirect the user to the home page or login page
        return redirect('/');
        // Or redirect to the login page: return redirect()->route('login');
    }
}
