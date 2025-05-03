@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Create New User</h2>

        <x-form :action="route('users.store')" method="POST">
            @csrf

            <div class="form-control">
                <x-label for="name" value="Name" />
                <x-input id="name" name="name" type="text" class="input input-bordered w-full" :value="old('name')" required autofocus />
                <x-error field="name" class="text-error text-sm mt-1" />
            </div>

            <div class="form-control mt-4">
                <x-label for="email" value="Email" />
                <x-input id="email" name="email" type="email" class="input input-bordered w-full" :value="old('email')" required />
                <x-error field="email" class="text-error text-sm mt-1" />
            </div>

            <div class="form-control mt-4">
                <x-label for="password" value="Password" />
                <x-input id="password" name="password" type="password" class="input input-bordered w-full" required />
                <x-error field="password" class="text-error text-sm mt-1" />
            </div>

            <div class="form-control mt-4">
                <x-label for="password_confirmation" value="Confirm Password" />
                <x-input id="password_confirmation" name="password_confirmation" type="password" class="input input-bordered w-full" required />
                <x-error field="password_confirmation" class="text-error text-sm mt-1" />
            </div>

            {{-- Optional: Role assignment on creation form --}}
            {{-- @if(isset($roles))
            <div class="form-control mt-4">
                <x-label for="role_id" value="Assign Role" />
                <select id="role_id" name="role_id" class="select select-bordered w-full">
                    <option value="">Select a role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <x-error field="role_id" class="text-error text-sm mt-1" />
            </div>
            @endif --}}


            <div class="card-actions justify-end mt-6">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                <x-form-button type="submit" class="btn btn-primary">Create User</x-form-button>
            </div>
        </x-form>
    </div>
</div>
@endsection
