<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Estadísticas de auditoría') }}
            </h2>

            <a href="{{ route('admin.audit.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                Ver registros
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 grid gap-4 md:grid-cols-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Eventos de hoy</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $todayTotal }}</div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Logins fallidos últimas 24h</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $recentLoginFailures }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Eventos por tipo</h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($totalsByEvent as $item)
                        <div class="flex items-center justify-between px-6 py-4 text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $item->event) }}</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->total }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Todavía no hay eventos registrados.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
