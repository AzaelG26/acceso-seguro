<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="text" name="email" :value="old('email')"  autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                             autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div> -->
        
        <div class="mt-4 flex flex-col items-center">
            <div>
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <!-- @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif -->

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Limpiar errores previos
                form.querySelectorAll('.js-error-msg').forEach(el => el.remove());
                form.querySelectorAll('input').forEach(el => el.classList.remove('border-red-500'));

                // Validar Email
                const emailInput = document.getElementById('email');
                // Sanitizar y forzar a minúsculas inmediatamente
                const emailVal = emailInput.value.replace(/<[^>]*>?/gm, '').trim().toLowerCase();
                emailInput.value = emailVal;
                
                // Regex más estricta (no permite puntos al final del nombre, ni dominios de 1 letra)
                const emailRegex = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9]){1,}$/;
                
                if (emailVal === '') {
                    showError(emailInput, 'El correo electrónico es obligatorio.');
                    isValid = false;
                } else if (!emailRegex.test(emailVal)) {
                    showError(emailInput, 'Ingresa un formato de correo electrónico válido.');
                    isValid = false;
                }

                // Validar Password
                const passwordInput = document.getElementById('password');
                if (passwordInput.value === '') {
                    showError(passwordInput, 'La contraseña es obligatoria.');
                    isValid = false;
                }

                // Validar reCAPTCHA
                const recaptchaResponse = document.querySelector('[name="g-recaptcha-response"]');
                if (recaptchaResponse && recaptchaResponse.value === '') {
                    const recaptchaContainer = document.querySelector('.g-recaptcha');
                    if (recaptchaContainer) {
                        showError(recaptchaContainer.parentElement, 'Por favor, completa el reCAPTCHA.');
                    }
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            function showError(input, message) {
                input.classList.add('border-red-500');
                let ul = document.createElement('ul');
                ul.className = 'mt-2 text-sm text-red-600 dark:text-red-400 js-error-msg';
                let li = document.createElement('li');
                li.textContent = message;
                ul.appendChild(li);
                input.parentNode.insertBefore(ul, input.nextSibling);
            }

            // Quitar el borde rojo al empezar a escribir
            document.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('border-red-500');
                    const err = this.parentNode.querySelector('.js-error-msg');
                    if (err) err.remove();
                });
            });
        });
    </script>
</x-guest-layout>
