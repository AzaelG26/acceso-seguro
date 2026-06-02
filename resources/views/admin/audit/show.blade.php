<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalle de auditoría') }}
            </h2>

            <a href="{{ route('admin.audit.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <dl class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @php($eventTime = $auditLog->{$timestampColumn})
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Fecha</dt>
                        <dd class="sm:col-span-2 text-gray-900 dark:text-gray-100">{{ $eventTime?->format('Y-m-d H:i:s') ?? 'N/D' }}</dd>
                    </div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Usuario</dt>
                        <dd class="sm:col-span-2 text-gray-900 dark:text-gray-100">
                            {{ $auditLog->user?->name ?? 'Sin usuario' }}
                            <span class="block text-xs text-gray-500">{{ $auditLog->user?->email ?? ($auditLog->metadata['email'] ?? 'N/D') }}</span>
                        </dd>
                    </div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Evento</dt>
                        <dd class="sm:col-span-2 text-gray-900 dark:text-gray-100">{{ str_replace('_', ' ', $auditLog->event ?? 'legacy') }}</dd>
                    </div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Acción</dt>
                        <dd class="sm:col-span-2 text-gray-900 dark:text-gray-100">{{ $auditLog->description ?? 'Registro anterior' }}</dd>
                    </div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">IP</dt>
                        <dd class="sm:col-span-2 text-gray-900 dark:text-gray-100">{{ $auditLog->ip_address ?? 'N/D' }}</dd>
                    </div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Navegador</dt>
                        <dd class="sm:col-span-2 break-words text-gray-900 dark:text-gray-100">{{ $auditLog->user_agent ?? 'N/D' }}</dd>
                    </div>
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-3">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Metadata</dt>
                        <dd class="sm:col-span-2">
                            <pre class="overflow-x-auto rounded bg-gray-100 p-4 text-xs text-gray-800 dark:bg-gray-900 dark:text-gray-200">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
