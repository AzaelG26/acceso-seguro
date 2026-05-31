<x-guest-layout>
    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Te enviamos un código de 6 dígitos a tu correo. Expira en 10 minutos.
        </div>

        <div>
            <x-input-label for="otp" :value="__('Código de verificación')" />
            <x-text-input id="otp" class="block mt-1 w-full tracking-widest text-center text-lg"
                type="text"
                name="otp"
                maxlength="6"
                inputmode="numeric"
                autofocus
                autocomplete="off" />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verificar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
