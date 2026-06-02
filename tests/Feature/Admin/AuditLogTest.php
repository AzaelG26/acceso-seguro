<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        AuditLog::create([
            'user_id' => $admin->id,
            'event' => 'login_success',
            'description' => 'Inició sesión correctamente',
            'occurred_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('login success')
            ->assertSee('Inició sesión correctamente');
    }

    public function test_non_admin_can_not_view_audit_logs(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.audit.index'))
            ->assertForbidden();
    }

    public function test_failed_login_is_recorded(): void
    {
        $user = User::factory()->create([
            'email' => 'audit@example.com',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'login_failed',
            'description' => 'Intento de login fallido',
        ]);
    }

    public function test_audit_panel_can_render_legacy_table_without_occurred_at(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        $admin = User::factory()->create(['role' => 'admin']);
        AuditLog::create();

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('Registro anterior');
    }
}
