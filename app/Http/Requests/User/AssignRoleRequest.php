<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // For authorization
use App\Models\User; // To access the user
use Illuminate\Validation\Rule; // For exists rule

/**
 * Form Request for assigning a role to a user.
 * Handles validation and authorization for role assignment.
 */
class AssignRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Requires user to be logged in and have permission to assign role to THIS user.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if (!Auth::check()) return false;
        $user = $this->route('user');
        // User must have permission to assign roles AND be authorized to assign role to THIS user
        // You might also need to check if the user is allowed to assign the SPECIFIC role requested
        return Auth::user()->can('assign roles') && Auth::user()->can('assignRole', $user); // Example combined
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Validate that the role exists in the 'roles' table
            'role_id' => ['required', 'exists:roles,id'],
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
            'role_id.required' => 'Vai trò là bắt buộc.',
            'role_id.exists' => 'Vai trò không tồn tại.',
        ];
    }
}
