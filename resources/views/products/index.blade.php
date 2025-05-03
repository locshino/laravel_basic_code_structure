@extends('layouts.app') {{-- Extend the main layout --}}

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Product List</h2>

        @can('create products') {{-- Check permission to create --}}
        <div class="mb-4">
            <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
        </div>
        @endcan

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        @can('view all products') {{-- Only show status column for manager/admin --}}
                        <th>Active</th>
                        @endcan
                        <th>Actions</th>
                    </tr>
                </thead> 
                <tbody>
                    @forelse ($products as $product)
                    <tr>
                        <td>
                            @if ($product->image_path)
                                <div class="avatar">
                                    <div class="w-12 h-12 mask mask-squircle">
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" />
                                    </div>
                                </div>
                            @else
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content mask mask-squircle w-12 h-12">
                                        <span class="text-xs">No Image</span>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category ? $product->category->name : 'N/A' }}</td>
                        <td>{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->stock }}</td>
                         @can('view all products')
                         <td>
                             <input type="checkbox" class="toggle toggle-primary" {{ $product->is_active ? 'checked' : '' }} disabled />
                         </td>
                         @endcan
                        <td>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">View</a>
                                @can('update', $product) {{-- Check permission using Policy --}}
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @endcan
                                @can('delete', $product) {{-- Check permission using Policy --}}
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
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
                        <td colspan="{{ Auth::check() && Auth::user()->can('view all products') ? 7 : 6 }}" class="text-center">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
