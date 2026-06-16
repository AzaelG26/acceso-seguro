<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los servicios de la aplicación.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializa los servicios de la aplicación.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Session::extend('secure-database', function ($app) {
            $table = $app['config']['session.table'];
            $lifetime = $app['config']['session.lifetime'];
            $connection = $app['db']->connection($app['config']['session.connection']);

            return new \App\Extensions\SecureDatabaseSessionHandler($connection, $table, $lifetime, $app);
        });
    }
}
