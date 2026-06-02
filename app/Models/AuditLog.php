<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * Obtiene el usuario asociado con este registro de auditoría.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Guarda un evento de auditoría usando el contexto de la petición.
     *
     * El método es defensivo para que la autenticación y las acciones de usuario
     * sigan funcionando aunque la tabla de auditoría no esté migrada.
     */
    public static function record(string $event, string $description, ?Request $request = null, array $metadata = []): ?self
    {
        $user = $request?->user();

        try {
            if (! Schema::hasTable('audit_logs')) {
                return null;
            }

            $attributes = [
                'user_id' => $user?->id ?? $metadata['user_id'] ?? null,
                'event' => $event,
                'description' => $description,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
                'metadata' => $metadata ?: null,
                'occurred_at' => now(),
            ];

            $attributes = array_filter(
                $attributes,
                fn (string $column): bool => Schema::hasColumn('audit_logs', $column),
                ARRAY_FILTER_USE_KEY
            );

            return self::create($attributes);
        } catch (Throwable $exception) {
            Log::error('No se pudo registrar auditoría', [
                'event' => $event,
                'description' => $description,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
