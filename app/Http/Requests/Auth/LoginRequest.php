<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;
use App\Models\User;
use App\Rules\Recaptcha;

class LoginRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara y sanitiza los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strip_tags($this->email),
        ]);
    }

    /**
     * Obtiene las reglas de validación aplicables a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => ['required', new Recaptcha],
        ];
    }

    /**
     * Intenta autenticar las credenciales de la petición.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->email)->first();


        if (!$user || ! Hash::check($this->password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            Log::warning('Login failed', [
                'event'     => 'AUTH_LOGIN_FAILED',
                'email'     => $this->email,
                'ip'        => $this->ip(),
                'timestamp' => now(),
            ]);

            AuditLog::record('login_failed', 'Intento de login fallido', $this, [
                'email' => $this->email,
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user->status === 'inactive') {
            Log::warning('Login attempt on inactive account', [
                'event'     => 'AUTH_INACTIVE_ACCOUNT',
                'email'     => $this->email,
                'ip'        => $this->ip(),
                'timestamp' => now(),
            ]);

            AuditLog::record('inactive_account_login', 'Intentó iniciar sesión con una cuenta inactiva', $this, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está inactiva.',
            ]);
        }

        Log::info('Login successful', [
            'email' => $this->email,
            'role'  => $user->role,
            'ip'    => $this->ip(),
            'timestamp' => now(),
        ]);

        RateLimiter::clear($this->throttleKey());

        if ($user->isUser()){
            session(['auth.id' => $user->id, 'auth.remember' => $this->boolean('remember')]);
        } elseif ($user->isAdmin()){
            session(['auth.id' => $user->id, 'auth.remember' => $this->boolean('remember')]);
        } else {
            Auth::login($user, $this->boolean('remember'));
        }
    }

    /**
     * Asegura que la petición de login no haya excedido el límite de intentos.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        Log::warning('Rate limit reached', [
            'event'     => 'AUTH_RATE_LIMIT',
            'email'     => $this->email,
            'ip'        => $this->ip(),
            'timestamp' => now(),
        ]);

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Obtiene la clave usada para limitar intentos de login.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
