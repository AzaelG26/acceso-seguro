<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Auditoría') }}
            </h2>

            <a href="{{ route('admin.audit.statistics') }}" class="text-sm font-medium text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-2xl">
                Ver estadísticas
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form

    method="GET"

    action="{{ route('admin.audit.index') }}"

    class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

    <div class="grid gap-4 md:grid-cols-[1fr_220px_auto]">

        <x-text-input

            name="search"

            value="{{ request('search') }}"

            placeholder="Buscar por usuario, correo, acción o IP"

            class="w-full"

        />

        <select

            name="event"

            class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">

            <option value="">Todos los eventos</option>

            @foreach ($events as $event)

                <option value="{{ $event }}" @selected(request('event') === $event)>

                    {{ str_replace('_', ' ', $event) }}

                </option>

            @endforeach

        </select>

        <button

            type="submit"

            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">

            Filtrar

        </button>

    </div>

</form>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="overflow-x-auto">

    <table class="min-w-[1200px] w-full">
                <thead>
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Fecha
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Usuario
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Evento
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Acción
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        IP
                    </th>

                    <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Opciones
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($logs as $log)
                    @php
                        $eventTime = $log->{$timestampColumn}?->timezone(config('app.timezone'));

                        $eventClass = match($log->event) {
                            'created' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'updated' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            'deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            default => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                        };
                    @endphp

                    <tr class="group transition-colors duration-150 hover:bg-black/20">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $eventTime?->format('d/m/Y') ?? 'N/D' }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $eventTime?->format('H:i:s') ?? '' }}
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $log->user?->name ?? 'Sin usuario' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $log->user?->email ?? ($log->metadata['email'] ?? 'N/D') }}
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $eventClass }}">
                                {{ str_replace('_', ' ', $log->event ?? 'legacy') }}
                            </span>
                        </td>

                        <td class="max-w-md px-6 py-4">
                            <p class="truncate text-gray-700 dark:text-gray-300">
                                {{ $log->description ?? 'Registro anterior' }}
                            </p>
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-gray-700 dark:text-gray-300">
                            {{ $log->ip_address ?? 'N/D' }}
                        </td>

                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <a
                                href="{{ route('admin.audit.show', $log) }}"
                                class="inline-flex items-center rounded-lg border bg-gray-50 dark:bg-gray-900 dark:text-gray-300 border-gray-200 dark:border-gray-700 shadow-sm dark:shadow-gray-900/50 px-3 py-2 text-xs font-semibold text-indigo-700 transition ">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="6"
                            class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No hay registros de auditoría.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($logs->hasPages())
    <div class="mt-6">
        {{ $logs->links() }}
    </div>
@endif

        </div>
    </div>
</x-app-layout>
