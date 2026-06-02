<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * Las URI que deben estar disponibles mientras el modo mantenimiento está activo.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
