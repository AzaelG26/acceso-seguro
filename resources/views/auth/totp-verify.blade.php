<x-guest-layout>
    <form method="POST" action="{{ route('totp.verify') }}">
        @csrf

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            <strong>Tercer Factor de Autenticación (Administrador)</strong><br>
            Ingresa el código de 6 dígitos de tu aplicación Google Authenticator.
        </div>

        <div>
            <x-input-label for="totp" :value="__('Código de Authenticator')" />
            <x-text-input id="totp" class="block mt-1 w-full tracking-widest text-center text-lg"
                type="text"
                name="totp"
                maxlength="6"
                inputmode="numeric"
                autofocus
                autocomplete="off" />
            <x-input-error :messages="$errors->get('totp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verificar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
