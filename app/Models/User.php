<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_known_ip',
        'otp_code',
        'otp_expires_at',
        'google2fa_secret',
    ];

    /**
     * Los atributos que deben ocultarse al serializar.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    /**
     * Los atributos que deben convertirse a tipos específicos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'otp_expires_at' => 'datetime',
    ];

    /**
     * Indica si el usuario tiene rol de administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Indica si el usuario tiene rol de usuario normal.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Indica si el usuario tiene rol de invitado.
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Indica si la cuenta del usuario está activa.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
