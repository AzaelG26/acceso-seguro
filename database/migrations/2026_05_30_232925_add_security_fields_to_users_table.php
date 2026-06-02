<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('guest')->after('password');
            $table->string('status')->default('active')->after('role');
            $table->string('last_known_ip')->nullable()->after('status');
            $table->string('otp_code')->nullable()->after('last_known_ip');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });

        // SQLite doesn't support named constraints on ALTER TABLE
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT check_role CHECK (role IN ('guest', 'user', 'admin'))");
            DB::statement("ALTER TABLE users ADD CONSTRAINT check_status CHECK (status IN ('active', 'inactive'))");
        }

    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS check_role");
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS check_status");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'last_known_ip', 'otp_code', 'otp_expires_at']);
        });
    }
};
