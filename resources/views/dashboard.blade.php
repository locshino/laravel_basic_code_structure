{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app') {{-- Extend the main layout --}}

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Dashboard</h2>

        <p>Welcome back, {{ Auth::user()->name }}!</p> {{-- Display authenticated user's name --}}

        <div class="mt-4">
            <h3 class="text-lg font-semibold mb-2">Quick Links:</h3>
            <ul class="list-disc list-inside">
                <li><a href="{{ route('products.index') }}" class="link link-primary">View Products</a></li>
                @can('manage categories') {{-- Check permission with Spatie/Gate --}}
                <li><a href="{{ route('categories.index') }}" class="link link-primary">Manage Categories</a></li>
                @endcan
                @can('manage users') {{-- Check permission with Spatie/Gate --}}
                <li><a href="{{ route('users.index') }}" class="link link-primary">Manage Users</a></li>
                @endcan
                {{-- Add more quick links based on roles/permissions --}}
            </ul>
        </div>

        {{-- You can add more content here, e.g., statistics, recent activity, etc. --}}
        {{-- Example: Display counts if passed from controller --}}
        {{-- @if(isset($productCount))
             <div class="mt-4">
                 <h3 class="text-lg font-semibold mb-2">Statistics:</h3>
                 <p>Total Products: {{ $productCount }}</p>
                 <p>Total Categories: {{ $categoryCount }}</p>
                 @can('manage users')
                 <p>Total Users: {{ $userCount }}</p>
                 @endcan
             </div>
        @endif --}}

    </div>
</div>
@endsection
