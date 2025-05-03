<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization
use Illuminate\Validation\Rules\Password; // For strong password rules
use App\Models\User; // To access the user being updated
use Illuminate\Validation\Rule; // For unique rule

/**
 * Form Request for updating an existing user.
 * Handles validation and authorization for user update.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to update this specific user.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if (!Auth::check()) return false;
        $user = request()->route('user');
        // User must have permission to update users AND be authorized to update THIS user
        return Auth::user()->can('update users') && Auth::user()->can('update', $user); // Example combined
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = request()->route('user')->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            // Email must be unique, ignore current user's email
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            // Password is optional on update, apply rules if provided
            'password' => ['nullable', 'confirmed', Password::defaults()],
            // You might include role_id or role_name here if updating role via this form
            // 'role_id' => ['nullable', 'exists:roles,id'],
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
            'name.required' => 'Tên người dùng là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
