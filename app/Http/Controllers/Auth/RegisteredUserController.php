<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Rules\Recaptcha;

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     *
     * @return \Illuminate\View\View Retorna la vista auth.register
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Procesa una petición entrante de registro de un nuevo usuario.
     *
     * Sanitiza el nombre y correo eliminando etiquetas HTML para prevenir XSS.
     * Valida los campos (con estrictas reglas de contraseña) e inserta el 
     * nuevo usuario con el rol 'guest' por defecto. Al finalizar, registra
     * la acción en la bitácora de auditoría.
     *
     * @param  \Illuminate\Http\Request  $request Petición con datos de registro
     * @return \Illuminate\Http\RedirectResponse Redirección al Dashboard
     * @throws \Illuminate\Validation\ValidationException Si las reglas de validación fallan
     */
    public function store(Request $request): RedirectResponse
    {
        // Sanitización explícita antes de validar (Back-end)
        $request->merge([
            'name' => strip_tags($request->name),
            'email' => strip_tags($request->email),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed',
                Rules\Password::min(12)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
            ],
            'g-recaptcha-response' => ['required', new Recaptcha],
        ]);

        $user = User::create([
            'name'     => strip_tags($request->name),
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'guest',
        ]);

        event(new Registered($user));

        Auth::login($user);

        AuditLog::record('user_registered', 'Se registró como nuevo usuario', $request, [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect(RouteServiceProvider::HOME);
    }
}
