<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Auditoría') }}
            </h2>

            <a href="{{ route('admin.audit.statistics') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                Ver estadísticas
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="mb-4 grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <x-text-input name="search" value="{{ request('search') }}" placeholder="Buscar por usuario, correo, acción o IP" />

                <select name="event" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Todos los eventos</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}" @selected(request('event') === $event)>
                            {{ str_replace('_', ' ', $event) }}
                        </option>
                    @endforeach
                </select>

                <x-primary-button>
                    Filtrar
                </x-primary-button>
            </form>

            <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Evento</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Acción</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">IP</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($logs as $log)
                                @php($eventTime = $log->{$timestampColumn})
                                <tr class="text-sm text-gray-700 dark:text-gray-300">
                                    <td class="whitespace-nowrap px-4 py-3">{{ $eventTime?->format('Y-m-d H:i:s') ?? 'N/D' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? 'Sin usuario' }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->user?->email ?? ($log->metadata['email'] ?? 'N/D') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                            {{ str_replace('_', ' ', $log->event ?? 'legacy') }}
                                        </span>
                                    </td>
                                    <td class="min-w-64 px-4 py-3">{{ $log->description ?? 'Registro anterior' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">{{ $log->ip_address ?? 'N/D' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <a href="{{ route('admin.audit.show', $log) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                            Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No hay registros de auditoría.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
