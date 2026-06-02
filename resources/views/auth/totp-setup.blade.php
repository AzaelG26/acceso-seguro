<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Configuración de Seguridad Administrador (3FA)') }}
    </div>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <p>Escanea el siguiente código QR con la aplicación <strong>Google Authenticator</strong> o Authy desde tu dispositivo móvil.</p>
    </div>

    <div class="flex justify-center mb-6">
        {!! $qrImage !!}
    </div>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400 text-center">
        <p>O ingresa este código manualmente:</p>
        <strong class="text-indigo-500 tracking-widest text-lg">{{ $secret }}</strong>
    </div>

    <form method="POST" action="{{ route('totp.confirm') }}">
        @csrf

        <div>
            <x-input-label for="totp" :value="__('Código generado por la App')" />
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
                {{ __('Confirmar y Entrar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
