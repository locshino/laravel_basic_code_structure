{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light"> {{-- DaisyUI theme --}}

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Tailwind CSS (with DaisyUI) and JS --}}

    {{-- Blade UI Kit assets (if needed, usually handled by package) --}}
    {{-- <x-blade-ui-kit::assets /> --}}

    <style>
        /* Optional: Adjust main content padding when sidebar is visible */
        /* This can also be done with Tailwind classes in the HTML */
        /* .has-sidebar main {
            padding-left: 1rem;
            padding-right: 1rem;
        } */
    </style>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-base-200"> {{-- DaisyUI background --}}
        {{-- Navigation (Example using DaisyUI Navbar) --}}
        <div class="navbar bg-base-100 shadow-xl">
            <div class="flex-1">
                <a class="btn btn-ghost text-xl" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
            </div>
            <div class="flex-none">
                <ul class="menu menu-horizontal px-1">
                    {{-- Add Dashboard link here, visible only when logged in --}}
                    @auth
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    @endauth

                    <li><a href="{{ route('products.index') }}">Products</a></li>

                    @auth {{-- Check if user is logged in --}}
                        @can('manage categories')
                            {{-- Check permission with Spatie/Gate --}}
                            <li><a href="{{ route('categories.index') }}">Categories</a></li>
                        @endcan
                        @can('manage users')
                            {{-- Check permission with Spatie/Gate --}}
                            <li><a href="{{ route('users.index') }}">Users</a></li>
                        @endcan
                        <li>
                            <details>
                                <summary>{{ Auth::user()->name }}</summary>
                                <ul class="p-2 bg-base-100 rounded-t-none z-[1]"> {{-- Add z-[1] to ensure dropdown is above other content --}}
                                    <li><a href="#">Profile</a></li> {{-- Placeholder --}}
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}"> {{-- Laravel Auth Logout --}}
                                            @csrf
                                            <button type="submit">Log Out</button>
                                        </form>
                                    </li>
                                </ul>
                            </details>
                        </li>
                    @else
                        {{-- User is not logged in --}}
                        <li><a href="{{ route('login') }}">Login</a></li> {{-- Link to login route --}}
                        {{-- Optional: Link to registration page --}}
                        {{-- <li><a href="{{ route('register') }}">Register</a></li> --}}
                    @endauth
                </ul>
            </div>
        </div>

        {{-- Page Content Area --}}
        <div class="flex"> {{-- Use flexbox to create sidebar and content layout --}}

            @auth
                {{-- Sidebar (Visible only on dashboard route) --}}
                <div class="w-64 bg-base-100 shadow-xl p-4 min-h-screen"> {{-- Fixed width sidebar --}}
                    <h3 class="text-lg font-semibold mb-4">Dashboard Menu</h3>
                    <ul class="menu bg-base-100 rounded-box"> {{-- DaisyUI menu component --}}
                        <li>
                            <a href="{{ route('dashboard') }}"
                                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                Dashboard Home
                            </a>
                        </li>
                        {{-- Add more sidebar links based on user roles/permissions --}}
                        @can('manage products')
                            <li>
                                <a href="{{ route('products.index') }}"
                                    class="{{ request()->routeIs('products.index') ? 'active' : '' }}">
                                    Manage Product
                                </a>
                            </li>
                        @endcan
                        @can('manage categories')
                            <li>
                                <a href="{{ route('categories.index') }}"
                                    class="{{ request()->routeIs('categories.index') ? 'active' : '' }}">
                                    Manage Categories
                                </a>
                            </li>
                        @endcan
                        @can('manage users')
                            <li>
                                <a href="{{ route('users.index') }}"
                                    class="{{ request()->routeIs('users.index') ? 'active' : '' }}">
                                    Manage Users
                                </a>
                            </li>
                        @endcan
                        {{-- Add other dashboard-specific links here --}}
                    </ul>
                </div>
            @endauth
            {{-- Main Content --}}
            <main class="flex-1 container mx-auto p-4"> {{-- flex-1 makes this area take remaining width --}}
                {{-- Display success/error messages from sessions --}}
                @if (session('success'))
                    <div role="alert" class="alert alert-success mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div role="alert" class="alert alert-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any()) {{-- Display validation errors --}}
                    <div role="alert" class="alert alert-error mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content') {{-- Content of specific pages goes here --}}
            </main>
        </div>
    </div>
</body>

</html>
