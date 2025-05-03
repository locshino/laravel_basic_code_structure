@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">User Details: {{ $user->name }}</h2>

        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Roles:</strong>
             @forelse ($user->getRoleNames() as $roleName)
                <span class="badge badge-info">{{ $roleName }}</span>
            @empty
                <span class="badge badge-warning">No Role</span>
            @endforelse
        </p>

        <div class="card-actions justify-end mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to List</a>
            @can('update', $user)
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">Edit</a>
            @endcan
            @can('assignRole', $user)
             <a href="{{ route('users.assign_role.form', $user->id) }}" class="btn btn-accent">Assign Role</a>
            @endcan
            @can('delete', $user)
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
