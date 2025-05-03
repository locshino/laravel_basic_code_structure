<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Form Request for handling login requests.
 * Validates input and handles rate limiting.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authentication requests do not require prior authorization.
     */
    public function authorize(): bool
    {
        return true; // Anyone can attempt to log in
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // Ensure we haven't exceeded the login attempt rate limit
        $this->ensureIsNotRateLimited();

        // Attempt to log the user in using the 'web' guard
        if (! Auth::attempt(request()->only('email', 'password'), request()->boolean('remember'))) {
            // If authentication fails, increment the rate limit counter
            RateLimiter::hit($this->throttleKey());

            // Throw a validation exception with a generic error message
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'), // Use Laravel's default translation for failed login
            ]);
        }

        // If authentication succeeds, clear any rate limiting for this user/IP
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Check if the rate limit has been exceeded for this user/IP
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) { // 5 attempts per minute
            return;
        }

        // Get the time until the next attempt is allowed
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Throw a validation exception indicating too many attempts
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        // If the request is rate limited, throw an exception with a custom message
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw new HttpResponseException(response()->json([
                'message' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
                'errors' => $validator->errors(),
            ], 422)->withHeaders([
                'Retry-After' => $seconds,
            ]));
        }

        // If not rate limited, proceed with the default validation failure response
        parent::failedValidation($validator);
    }

    /**
     * Get the rate limiting throttle key for the request.
     * Uses email and IP address to uniquely identify the attempt.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower(request()->string('email')) . '|' . request()->ip());
    }
}
