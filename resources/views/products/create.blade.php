@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Create New Product</h2>

        {{-- Use Blade UI Kit form component --}}
        <x-form :action="route('products.store')" method="POST" enctype="multipart/form-data"> {{-- enctype for file upload --}}
            @csrf {{-- CSRF token --}}

            <div class="form-control">
                <x-label for="name" value="Product Name" />
                <x-input id="name" name="name" type="text" class="input input-bordered w-full" :value="old('name')" required autofocus />
                <x-error field="name" class="text-error text-sm mt-1" />
            </div>

             {{-- Optional: Slug field if you want manual slug input --}}
             {{-- <div class="form-control mt-4">
                <x-label for="slug" value="Slug (Optional)" />
                <x-input id="slug" name="slug" type="text" class="input input-bordered w-full" :value="old('slug')" />
                <x-error field="slug" class="text-error text-sm mt-1" />
             </div> --}}

            <div class="form-control mt-4">
                <x-label for="category_id" value="Category (Optional)" />
                <select id="category_id" name="category_id" class="select select-bordered w-full">
                    <option value="">Select a category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <x-error field="category_id" class="text-error text-sm mt-1" />
            </div>

            <div class="form-control mt-4">
                <x-label for="price" value="Price" />
                <x-input id="price" name="price" type="number" step="0.01" class="input input-bordered w-full" :value="old('price')" required />
                <x-error field="price" class="text-error text-sm mt-1" />
            </div>

            <div class="form-control mt-4">
                <x-label for="stock" value="Stock" />
                <x-input id="stock" name="stock" type="number" class="input input-bordered w-full" :value="old('stock')" required />
                <x-error field="stock" class="text-error text-sm mt-1" />
            </div>

            <div class="form-control mt-4">
                <x-label for="description" value="Description (Optional)" />
                <x-textarea id="description" name="description" class="textarea textarea-bordered w-full">{{ old('description') }}</x-textarea>
                <x-error field="description" class="text-error text-sm mt-1" />
            </div>

             <div class="form-control mt-4">
                <x-label for="image" value="Product Image (Optional)" />
                <x-input id="image" name="image" type="file" class="file-input file-input-bordered w-full" />
                <x-error field="image" class="text-error text-sm mt-1" />
            </div>

            {{-- is_active field, only shown for users with 'view all products' permission (manager/admin) --}}
            @can('view all products')
            <div class="form-control mt-4">
                <x-label for="is_active" value="Is Active?" />
                 <input type="hidden" name="is_active" value="0"> {{-- Hidden field for unchecked state --}}
                <input type="checkbox" id="is_active" name="is_active" class="toggle toggle-primary" value="1" {{ old('is_active', true) ? 'checked' : '' }} />
                <x-error field="is_active" class="text-error text-sm mt-1" />
            </div>
            @endcan


            <div class="card-actions justify-end mt-6">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                <x-form-button type="submit" class="btn btn-primary">Create Product</x-form-button>
            </div>
        </x-form>
    </div>
</div>
@endsection
