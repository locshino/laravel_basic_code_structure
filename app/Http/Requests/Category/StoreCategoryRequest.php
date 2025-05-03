<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization
use Illuminate\Validation\Rule; // For unique rule

/**
 * Form Request for storing a new category.
 * Handles validation and authorization for category creation.
 */
class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to create categories.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if (!Auth::check()) return false;
        return Auth::user()->can('create categories'); // Example using Spatie
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')], // Name must be unique
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')], // Slug must be unique if provided
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên thể loại là bắt buộc.',
            'name.unique' => 'Tên thể loại đã tồn tại.',
            'slug.unique' => 'Slug thể loại đã tồn tại.',
        ];
    }
}
