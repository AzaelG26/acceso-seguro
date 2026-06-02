<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de eventos a listeners de la aplicación.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Registra eventos de la aplicación.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determina si los eventos y listeners deben descubrirse automáticamente.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
