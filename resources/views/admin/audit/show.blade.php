<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalle de auditoría') }}
            </h2>

            <a href="{{ route('admin.audit.index') }}" class="text-sm font-medium text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-2xl">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @php
    $eventTime = $auditLog->{$timestampColumn}?->timezone(config('app.timezone'));

    $eventClass = match($auditLog->event) {
        'created' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'updated' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'deleted' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        default => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
    };
@endphp

<div class="space-y-6">

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Resumen del evento
            </h3>
        </div>

        <div class="grid gap-6 p-6 md:grid-cols-2">

            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">
                    Fecha
                </p>

                <p class="mt-2 text-gray-900 dark:text-white">
                    {{ $eventTime?->format('d/m/Y H:i:s') ?? 'N/D' }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">
                    Evento
                </p>

                <div class="mt-2">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $eventClass }}">
                        {{ str_replace('_', ' ', $auditLog->event ?? 'legacy') }}
                    </span>
                </div>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">
                    Usuario
                </p>

                <p class="mt-2 font-semibold text-gray-900 dark:text-white">
                    {{ $auditLog->user?->name ?? 'Sin usuario' }}
                </p>

                <p class="text-sm text-gray-500">
                    {{ $auditLog->user?->email ?? ($auditLog->metadata['email'] ?? 'N/D') }}
                </p>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500">
                    Dirección IP
                </p>

                <p class="mt-2 text-gray-900 dark:text-white">
                    {{ $auditLog->ip_address ?? 'N/D' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs uppercase tracking-wider text-gray-500">
                    Acción
                </p>

                <p class="mt-2 text-gray-900 dark:text-white">
                    {{ $auditLog->description ?? 'Registro anterior' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs uppercase tracking-wider text-gray-500">
                    Navegador / User Agent
                </p>

                <div class="mt-2 rounded-lg bg-gray-100 p-4 text-sm text-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    {{ $auditLog->user_agent ?? 'N/D' }}
                </div>
            </div>

        </div>
    </div>

    @php
    $metadata = $auditLog->metadata;
@endphp

@if(!empty($metadata))

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Metadata
        </h3>
    </div>

    <div class="divide-y divide-gray-200 dark:divide-gray-700">

        @foreach($metadata as $key => $value)

            <div class="grid gap-2 px-6 py-4 md:grid-cols-3">

                <div class="font-medium text-gray-500 dark:text-gray-400">
                    {{ Str::headline($key) }}
                </div>

                <div class="md:col-span-2 text-gray-900 dark:text-white break-words">

                    @if(is_bool($value))
                        {{ $value ? 'Sí' : 'No' }}

                    @elseif(is_array($value))
                        <div class="flex flex-wrap gap-2">
                            @foreach($value as $item)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs dark:bg-gray-700">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>

                    @elseif(is_null($value))
                        <span class="text-gray-400">
                            N/D
                        </span>

                    @else
                        {{ $value }}
                    @endif

                </div>

            </div>

        @endforeach

    </div>

</div>

@endif

</div>
        </div>
    </div>
</x-app-layout>
