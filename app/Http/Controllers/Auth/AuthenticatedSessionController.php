<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\Auth\OtpController;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;


class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra la vista de inicio de sesión.
     *
     * @return \Illuminate\View\View Retorna la vista auth.login
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa una petición entrante de autenticación (Login).
     *
     * Valida las credenciales, regenera la sesión para evitar Session Fixation,
     * verifica si el usuario tiene doble factor (OTP) activo, y finalmente
     * registra el evento en la auditoría si el inicio fue exitoso.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request Petición con datos de login
     * @return \Illuminate\Http\RedirectResponse Redirección al Home o a la vista OTP
     * @throws \Illuminate\Validation\ValidationException Si la validación falla
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if(session('auth.id')){
            $user = User::findOrFail(session('auth.id'));
            (new OtpController)->send($user);
            return redirect()->route('otp.show');
        }

        AuditLog::record('login_success', 'Inició sesión correctamente', $request);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destruye la sesión autenticada actual (Logout).
     *
     * Invalida la sesión actual, regenera el token CSRF para evitar ataques
     * y elimina las cookies explícitamente del navegador web.
     *
     * @param  \Illuminate\Http\Request  $request Petición HTTP actual
     * @return \Illuminate\Http\RedirectResponse Redirección a la ruta raíz ('/')
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Borrar explícitamente las cookies del navegador para evitar que quede "basura"
        $cookieSession = Cookie::forget(config('session.cookie'));
        $cookieXsrf = Cookie::forget('XSRF-TOKEN');

        return redirect('/')->withCookie($cookieSession)->withCookie($cookieXsrf);
    }
}
