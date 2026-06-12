<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"  autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"  autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                             autocomplete="new-password" />

            @if($errors->has('password'))
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ $errors->first('password') }}
                </p>
            @endif
            <p class="mt-1 text-xs text-gray-500">
                Mínimo 12 caracteres, mayúsculas, minúsculas, números y símbolos.
            </p>
            <ul class="mt-2 text-xs space-y-1" id="password-rules">
                <li id="rule-length"  class="text-gray-400">✗ Mínimo 12 caracteres</li>
                <li id="rule-upper"   class="text-gray-400">✗ Una mayúscula</li>
                <li id="rule-lower"   class="text-gray-400">✗ Una minúscula</li>
                <li id="rule-number"  class="text-gray-400">✗ Un número</li>
                <li id="rule-symbol"  class="text-gray-400">✗ Un símbolo (@#$%!)</li>
            </ul>
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"  autocomplete="new-password" />

            <p id="confirm-match" class="mt-1 text-xs"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>



        <div class="mt-4 flex flex-col items-center">
            <div>
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                form.querySelectorAll('.js-error-msg').forEach(el => el.remove());
                form.querySelectorAll('input').forEach(el => el.classList.remove('border-red-500'));

                // Validar Nombre
                const nameInput = document.getElementById('name');
                const nameVal = nameInput.value.replace(/<[^>]*>?/gm, '').trim();
                nameInput.value = nameVal;
                if (nameVal === '') {
                    showError(nameInput, 'El nombre es obligatorio.');
                    isValid = false;
                }

                // Validar Email
                const emailInput = document.getElementById('email');
                const emailVal = emailInput.value.replace(/<[^>]*>?/gm, '').trim();
                emailInput.value = emailVal;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (emailVal === '') {
                    showError(emailInput, 'El correo electrónico es obligatorio.');
                    isValid = false;
                } else if (!emailRegex.test(emailVal)) {
                    showError(emailInput, 'Ingresa un formato de correo electrónico válido.');
                    isValid = false;
                }

                // Validar Password
                const passwordInput = document.getElementById('password');
                const passVal = passwordInput.value;
                if (passVal === '') {
                    showError(passwordInput, 'La contraseña es obligatoria.');
                    isValid = false;
                } else {
                    let passErrors = [];
                    if (passVal.length < 12) passErrors.push('mínimo 12 caracteres');
                    if (!/[A-Z]/.test(passVal)) passErrors.push('una mayúscula');
                    if (!/[a-z]/.test(passVal)) passErrors.push('una minúscula');
                    if (!/[0-9]/.test(passVal)) passErrors.push('un número');
                    if (!/[^a-zA-Z0-9]/.test(passVal)) passErrors.push('un símbolo');
                    
                    if (passErrors.length > 0) {
                        showError(passwordInput, 'Debe contener: ' + passErrors.join(', ') + '.');
                        isValid = false;
                    }
                }

                // Validar Confirmación
                const confirmInput = document.getElementById('password_confirmation');
                if (confirmInput.value === '') {
                    showError(confirmInput, 'Confirma tu contraseña.');
                    isValid = false;
                } else if (confirmInput.value !== passVal) {
                    showError(confirmInput, 'Las contraseñas no coinciden.');
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
        });

        document.getElementById('password').addEventListener('input', function() {
            const val = this.value;

            check('rule-length', val.length >= 12);
            check('rule-upper',  /[A-Z]/.test(val));
            check('rule-lower',  /[a-z]/.test(val));
            check('rule-number', /[0-9]/.test(val));
            check('rule-symbol', /[\W_]/.test(val));
        });

        function check(id, passed) {
            const el = document.getElementById(id);
            el.textContent = (passed ? '✓ ' : '✗ ') + el.textContent.slice(2);
            el.className = passed ? 'text-green-500 dark:text-green-400' : 'text-gray-400 dark:text-gray-500';
        }

        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm  = this.value;
            const el       = document.getElementById('confirm-match');

            if (confirm.length === 0) {
                el.textContent = '';
                return;
            }

            if (password === confirm) {
                el.textContent = '✓ Las contraseñas coinciden';
                el.className = 'text-green-500 dark:text-green-400 text-xs mt-1';
            } else {
                el.textContent = '✗ Las contraseñas no coinciden';
                el.className = 'text-red-500 dark:text-red-400 text-xs mt-1';
            }
        });
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
                const err = this.parentNode.querySelector('.js-error-msg');
                if (err) err.remove();
            });
        });
    </script>
</x-guest-layout>
