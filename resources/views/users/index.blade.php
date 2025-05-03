@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">User List</h2>

        @can('create users') {{-- Check permission to create --}}
        <div class="mb-4">
            <a href="{{ route('users.create') }}" class="btn btn-primary">Add New User</a>
        </div>
        @endcan

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse ($user->getRoleNames() as $roleName) {{-- Spatie method --}}
                                <span class="badge badge-info">{{ $roleName }}</span>
                            @empty
                                <span class="badge badge-warning">No Role</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
                                @can('update', $user) {{-- Check permission using Policy --}}
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @endcan
                                @can('assignRole', $user) {{-- Check permission using Policy --}}
                                 <a href="{{ route('users.assign_role.form', $user->id) }}" class="btn btn-sm btn-accent">Assign Role</a>
                                @endcan
                                @can('delete', $user) {{-- Check permission using Policy --}}
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-error">Delete</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
