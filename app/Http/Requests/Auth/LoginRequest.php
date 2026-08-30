<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
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
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');
        $user = User::where('email', $email)->first();

        // Check if user exists and is blocked
        if ($user && $user->isBlocked()) {
            RateLimiter::hit($this->throttleKey(), 900);

            throw ValidationException::withMessages([
                'email' => __('auth.blocked'),
            ]);
        }

        // Attempt normal authentication
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 900);

            if ($user) {
                $user->increment('failed_login_attempts');
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        /** @var User $authenticatedUser */
        $authenticatedUser = Auth::user();

        // Check if user is blocked (in case changed between lookup and attempt)
        if ($authenticatedUser->isBlocked()) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey(), 900);

            throw ValidationException::withMessages([
                'email' => __('auth.blocked'),
            ]);
        }

        // Check if user verified their email (FR-004)
        if (! $authenticatedUser->isVerified()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('auth.unverified'),
            ]);
        }

        // Reset failed login attempts on successful login
        $authenticatedUser->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
