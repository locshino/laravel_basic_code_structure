<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization
use App\Models\Product; // To access the product being updated
use Illuminate\Validation\Rule; // For unique rule

/**
 * Form Request for updating an existing product.
 * Handles validation and authorization for product update.
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to update this specific product.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return false;
        }

        // Get the product instance being updated from the route parameters
        // Route model binding automatically injects the Product model based on {product} in the route
        $product = request()->route('product');

        // Check if the authenticated user has the 'update products' permission
        // AND if they are authorized to update THIS specific product (using a Policy)
        // Assumes you are using Laravel Gates/Policies or Spatie Permissions
        return Auth::user()->can('update products') && Auth::user()->can('update', $product); // Example combined check
        // Using only a Laravel Policy: return $this->user()->can('update', $product);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Get the product ID from the route parameters for unique rule exceptions
        $productId = request()->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'], // Image is optional on update
            'is_active' => ['boolean'], // Optional field
            // Slug should be unique, but ignore the current product's slug
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
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
            'slug.unique' => 'Slug sản phẩm đã tồn tại.',
        ];
    }
}
