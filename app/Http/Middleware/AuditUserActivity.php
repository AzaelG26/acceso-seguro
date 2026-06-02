<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditUserActivity
{
    /**
     * Registra acciones de escritura autenticadas sin auditar el panel de auditoría.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $shouldRecord = $request->user()
            && ! $request->is('admin/audit*')
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        if ($shouldRecord) {
            AuditLog::record('user_action', $this->descriptionFor($request), $request, [
                'method' => $request->method(),
                'path' => $request->path(),
                'route' => $request->route()?->getName(),
            ]);
        }

        return $next($request);
    }

    /**
     * Traduce rutas conocidas a descripciones legibles para auditoría.
     */
    private function descriptionFor(Request $request): string
    {
        return match ($request->route()?->getName()) {
            'profile.update' => 'Actualizó su perfil',
            'profile.destroy' => 'Eliminó su cuenta',
            'password.update' => 'Actualizó su contraseña',
            'logout' => 'Cerró sesión',
            default => 'Realizó una acción en '.$request->path(),
        };
    }
}
