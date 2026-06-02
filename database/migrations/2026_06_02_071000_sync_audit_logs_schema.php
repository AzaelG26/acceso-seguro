<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sincroniza tablas de auditoría parcialmente migradas con el esquema esperado.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('audit_logs', 'event')) {
                $table->string('event')->default('legacy');
            }

            if (! Schema::hasColumn('audit_logs', 'description')) {
                $table->string('description')->default('Registro anterior');
            }

            if (! Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address')->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->string('user_agent', 500)->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'metadata')) {
                $table->json('metadata')->nullable();
            }

            if (! Schema::hasColumn('audit_logs', 'occurred_at')) {
                $table->timestamp('occurred_at')->useCurrent();
            }
        });
    }

    /**
     * Esta migración solo repara diferencias de esquema y no elimina columnas.
     */
    public function down(): void
    {
        //
    }
};
