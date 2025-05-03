<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization
use App\Models\Category; // To access the category being updated
use Illuminate\Validation\Rule; // For unique rule

/**
 * Form Request for updating an existing category.
 * Handles validation and authorization for category update.
 */
class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to update this specific category.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if (!Auth::check()) return false;
        $category = request()->route('category');
        return Auth::user()->can('update categories') && Auth::user()->can('update', $category); // Example combined
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $categoryId = request()->route('category')->id;
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($categoryId)], // Unique ignore current
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)], // Unique ignore current
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
