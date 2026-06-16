<?php

namespace App\Extensions;

use Illuminate\Session\DatabaseSessionHandler;

class SecureDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * Destruye una sesión sin usar el comando DELETE.
     * En su lugar, limpia la carga útil (payload), desvincula al usuario y
     * marca la última actividad en 0 para "inactivar" la sesión.
     *
     * @param  string  $sessionId
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $this->getQuery()->where('id', $sessionId)->update([
            'payload' => '',
            'last_activity' => 0,
            'user_id' => null,
        ]);

        return true;
    }

    /**
     * Limpia las sesiones expiradas (Garbage Collection) sin usar DELETE.
     * Evita que el limpiador aleatorio de Laravel tire un Error 500 y en
     * su lugar "inactiva" las sesiones expiradas mediante un UPDATE.
     *
     * @param  int  $lifetime
     * @return int
     */
    public function gc($lifetime): int
    {
        return $this->getQuery()
            ->where('last_activity', '<=', time() - ($lifetime * 60))
            ->where('payload', '!=', '') // Solo actualizar las que no estén vacías
            ->update([
                'payload' => '',
                'last_activity' => 0,
                'user_id' => null,
            ]);
    }
}
