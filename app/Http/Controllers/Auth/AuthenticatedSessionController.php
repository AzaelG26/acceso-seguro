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
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa una petición entrante de autenticación.
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
     * Destruye la sesión autenticada actual.
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
