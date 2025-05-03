@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Assign Role to User: {{ $user->name }}</h2>

        <p>Current Roles:
             @forelse ($user->getRoleNames() as $roleName)
                <span class="badge badge-info">{{ $roleName }}</span>
            @empty
                <span class="badge badge-warning">No Role</span>
            @endforelse
        </p>

        <x-form :action="route('users.assign_role', $user->id)" method="POST">
            @csrf
            @method('PUT') {{-- Use PUT for updating roles --}}

            @if(isset($roles))
            <div class="form-control mt-4">
                <x-label for="role_id" value="Select Role to Assign" />
                <select id="role_id" name="role_id" class="select select-bordered w-full" required>
                    <option value="">-- Select a role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <x-error field="role_id" class="text-error text-sm mt-1" />
            </div>
            @else
                 <p class="text-warning">No roles available to assign. Please create roles first.</p>
            @endif


            <div class="card-actions justify-end mt-6">
                <a href="{{ route('users.show', $user->id) }}" class="btn btn-secondary">Cancel</a>
                 @if(isset($roles) && $roles->isNotEmpty())
                <x-form-button type="submit" class="btn btn-primary">Assign Role</x-form-button>
                 @endif
            </div>
        </x-form>
    </div>
</div>
@endsection
