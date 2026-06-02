<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Procesa una petición entrante y agrega encabezados de seguridad.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Evita clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Evita que el navegador adivine el tipo de contenido
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protección XSS en navegadores antiguos
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // No enviar referrer a otros dominios
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Solo cargar recursos del mismo origen
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'");

        // Forzar siempre conexiones HTTPS (HSTS) - 1 año de duración
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
