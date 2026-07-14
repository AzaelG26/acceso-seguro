<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::asForm()->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => config('services.recaptcha.secret_key'),
                        'response' => $value,
                        'remoteip' => request()->ip(),
                    ]
                );
            if (!$response->json('success')) {
                $fail('reCAPTCHA inválido');
            }
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA service unavailable', [
                'error' => $e->getMessage(),
            ]);
            $fail('No se pudo validar reCAPTCHA, intenta más tarde');
        }
    }
}
