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
            $table->enum('role', ['guest', 'user', 'admin'])->default('guest')->after('password');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('role');
            $table->string('last_known_ip')->nullable()->after('status');
            $table->string('otp_code')->nullable()->after('last_known_ip');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });

    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'last_known_ip', 'otp_code', 'otp_expires_at']);
        });
    }
};
