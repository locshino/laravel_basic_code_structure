@extends('layouts.app')

@section('content')
<div class="card shadow-xl bg-base-100">
    <div class="card-body">
        <h2 class="card-title">Create New Category</h2>

        <x-form :action="route('categories.store')" method="POST">
            @csrf

            <div class="form-control">
                <x-label for="name" value="Category Name" />
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
                <x-label field="description" value="Description (Optional)" />
                <x-textarea id="description" name="description" class="textarea textarea-bordered w-full">{{ old('description') }}</x-textarea>
                <x-error field="description" class="text-error text-sm mt-1" />
            </div>

            <div class="card-actions justify-end mt-6">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
                <x-form-button type="submit" class="btn btn-primary">Create Category</x-form-button>
            </div>
        </x-form>
    </div>
</div>
@endsection
