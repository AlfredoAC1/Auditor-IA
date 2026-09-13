<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    /**
     * Maximum allowed login attempts before lockout.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Lockout decay in seconds (1 minute).
     */
    private const DECAY_SECONDS = 60;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the login request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Custom Spanish error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'Ingresa tu contraseña.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials with dual-key rate limiting.
     *
     * Protects against:
     * - Credential stuffing & brute-force against a single account (Email + IP key).
     * - Password spraying across multiple accounts from a single source (IP-only key).
     * - Account enumeration (generic error messages).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember', true);

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);
            RateLimiter::hit($this->ipThrottleKey(), self::DECAY_SECONDS);

            Log::warning('Authentication failed.', [
                'email' => Str::mask($this->string('email')->toString(), '*', 3),
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no son válidas.',
            ]);
        }

        // Reset rate limit counters upon successful login
        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->ipThrottleKey());

        Log::info('User authenticated successfully.', [
            'user_id' => Auth::id(),
            'ip' => $this->ip(),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $tooManyAccountAttempts = RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS);
        $tooManyIpAttempts = RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::MAX_ATTEMPTS * 2);

        if (! $tooManyAccountAttempts && ! $tooManyIpAttempts) {
            return;
        }

        event(new Lockout($this));

        $activeKey = $tooManyAccountAttempts ? $this->throttleKey() : $this->ipThrottleKey();
        $seconds = RateLimiter::availableIn($activeKey);

        Log::notice('Login throttled due to excessive failed attempts.', [
            'key' => $activeKey,
            'seconds_remaining' => $seconds,
            'ip' => $this->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos de acceso fallidos. Por favor inténtalo de nuevo en {$seconds} segundos.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the email and IP combination.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip());
    }

    /**
     * Get the rate limiting throttle key for the IP address alone (password spraying defense).
     */
    public function ipThrottleKey(): string
    {
        return 'login_spray_ip:'.$this->ip();
    }
}
