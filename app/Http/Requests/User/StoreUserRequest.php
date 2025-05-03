<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization
use Illuminate\Validation\Rules\Password; // For strong password rules
use Illuminate\Validation\Rule; // For unique rule

/**
 * Form Request for storing a new user.
 * Handles validation and authorization for user creation.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to create users.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if (!Auth::check()) return false;
        return Auth::user()->can('create users'); // Example using Spatie
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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')], // Email must be unique
            'password' => ['required', 'confirmed', Password::defaults()], // Strong password and confirmation
            // You might include role_id or role_name here if assigning role on creation
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
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
