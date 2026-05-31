<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

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
                            name="password_confirmation" required autocomplete="new-password" />

            <p id="confirm-match" class="mt-1 text-xs"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
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

    </script>
</x-guest-layout>
