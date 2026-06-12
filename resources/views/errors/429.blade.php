<x-guest-layout>
    <div class="text-center py-6">
        <h1 class="text-6xl font-bold text-orange-500 mb-4">429</h1>
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Demasiadas Peticiones</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Has realizado demasiados intentos en muy poco tiempo. Nuestro firewall te ha bloqueado temporalmente.
            Por favor, espera unos minutos y vuelve a intentarlo.
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Regresar al inicio
        </a>
    </div>
</x-guest-layout>
