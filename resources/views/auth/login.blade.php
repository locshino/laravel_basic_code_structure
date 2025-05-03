@extends('layouts.app') {{-- Extend the main layout --}}

@section('content')
    <div class="flex justify-center items-center min-h-screen"> {{-- Center the card --}}
        <div class="card w-96 bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-center">Login</h2>

                {{-- Use Blade UI Kit form component --}}
                <x-form :action="route('login')" method="POST">
                    @csrf {{-- CSRF token --}}

                    <div class="form-control">
                        <x-label for="email" value="Email" />
                        <x-input id="email" name="email" type="email" class="input input-bordered w-full"
                            :value="old('email')" required autofocus autocomplete="username" />
                        <x-error field="email" class="text-error text-sm mt-1" />
                    </div>

                    <div class="form-control mt-4">
                        <x-label for="password" value="Password" />
                        <x-input id="password" name="password" type="password" class="input input-bordered w-full" required
                            autocomplete="current-password" />
                        <x-error field="password" class="text-error text-sm mt-1" />
                    </div>

                    <div class="form-control mt-4">
                        <label for="remember" class="label cursor-pointer">
                            <span class="label-text">Remember me</span>
                            <input id="remember" name="remember" type="checkbox" class="checkbox checkbox-primary" />
                        </label>
                    </div>

                    <div class="card-actions justify-end mt-6">
                        {{-- Optional: Forgot password link --}}
                        {{-- @if (Route::has('password.request'))
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            Forgot your password?
                        </a>
                    @endif --}}
                        <x-form-button type="submit" class="btn btn-primary">Log In</x-form-button>
                    </div>
                </x-form>

                {{-- Optional: Link to registration page --}}
                {{-- <div class="text-center mt-4">
                 Don't have an account? <a href="{{ route('register') }}" class="link link-primary">Register</a>
             </div> --}}

            </div>
        </div>
    </div>
@endsection
