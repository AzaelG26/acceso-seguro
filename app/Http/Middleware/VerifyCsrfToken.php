<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Las URI que deben excluirse de la verificación CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
