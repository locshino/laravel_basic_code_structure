@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Category List</h2>

        @can('create categories') {{-- Check permission to create --}}
        <div class="mb-4">
            <a href="{{ route('categories.create') }}" class="btn btn-primary">Add New Category</a>
        </div>
        @endcan

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ Str::limit($category->description, 50) ?? 'N/A' }}</td> {{-- Use Str::limit for description --}}
                        <td>
                            <div class="flex items-center space-x-2">
                                {{-- No 'show' view for categories in this basic example --}}
                                @can('update', $category) {{-- Check permission using Policy --}}
                                <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @endcan
                                @can('delete', $category) {{-- Check permission using Policy --}}
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
                        <td colspan="4" class="text-center">No categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
