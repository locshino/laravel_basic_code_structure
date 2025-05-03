@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">{{ $product->name }}</h2>

        @if ($product->image_path)
            <figure class="mb-4">
                 <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="rounded-lg max-w-xs h-auto"/>
            </figure>
        @endif

        <p><strong>Category:</strong> {{ $product->category ? $product->category->name : 'N/A' }}</p>
        <p><strong>Price:</strong> {{ number_format($product->price, 2) }}</p>
        <p><strong>Stock:</strong> {{ $product->stock }}</p>
        <p><strong>Description:</strong> {{ $product->description ?? 'No description provided.' }}</p>

        @can('view all products') {{-- Only show status for manager/admin --}}
        <p><strong>Status:</strong> {{ $product->is_active ? 'Active' : 'Inactive' }}</p>
        @endcan

        <div class="card-actions justify-end mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Back to List</a>
            @can('update', $product)
            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">Edit</a>
            @endcan
            @can('delete', $product)
            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
