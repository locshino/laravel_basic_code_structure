<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization

/**
 * Form Request for storing a new product.
 * Handles validation and authorization for product creation.
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to create products.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return false;
        }

        // Check if the authenticated user has the 'create products' permission
        // Assumes you are using Laravel Gates/Policies or Spatie Permissions
        return Auth::user()->can('create products'); // Example using Spatie Permission syntax
        // Or using a Laravel Policy: return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'], // Check if category_id exists in categories table
            'image' => ['nullable', 'image', 'max:2048'], // Validate image file (max 2MB)
            'is_active' => ['boolean'], // Optional field, only for admin/manager
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
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.max' => 'Tên sản phẩm không được vượt quá :max ký tự.',
            'price.required' => 'Giá sản phẩm là bắt buộc.',
            'price.numeric' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm không được âm.',
            'stock.required' => 'Số lượng tồn kho là bắt buộc.',
            'stock.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'stock.min' => 'Số lượng tồn kho không được âm.',
            'category_id.exists' => 'Thể loại sản phẩm không tồn tại.',
            'image.image' => 'File tải lên phải là hình ảnh.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá :max KB.',
        ];
    }
}
