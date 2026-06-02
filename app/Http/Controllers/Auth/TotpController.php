<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Facades\Log;

class TotpController extends Controller
{
    /**
     * Muestra la vista de configuración TOTP con código QR.
     */
    public function setup()
    {
        if (!session('auth.id') || !session('auth.2fa_passed')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail(session('auth.id'));

        // Si el usuario ya tiene secreto, no debería estar en esta vista.
        if ($user->google2fa_secret) {
            return redirect()->route('totp.show');
        }

        $google2fa = new Google2FA();
        
        // Genera un nuevo secreto.
        $secret = $google2fa->generateSecretKey();
        
        // Guarda el secreto en sesión hasta confirmarlo.
        session(['totp_setup_secret' => $secret]);

        // Genera la imagen QR usando la librería extendida.
        $qrImage = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.totp-setup', compact('secret', 'qrImage'));
    }

    /**
     * Confirma la configuración validando el primer código TOTP.
     *
     * Si el código es válido, guarda el secreto cifrado, finaliza el inicio
     * de sesión y registra el resultado en auditoría.
     */
    public function confirmSetup(Request $request)
    {
        $request->validate(['totp' => 'required|digits:6']);

        $user = User::findOrFail(session('auth.id'));
        $secret = session('totp_setup_secret');

        if (!$secret) {
            AuditLog::record('totp_setup_expired', 'Expiró la sesión de configuración TOTP', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()->route('totp.setup')->withErrors(['totp' => 'La sesión de configuración expiró. Intenta de nuevo.']);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $request->totp);

        if ($valid) {
            // Guarda el secreto en el usuario.
            $user->google2fa_secret = encrypt($secret);
            $user->save();

            // Limpia la sesión de configuración.
            session()->forget('totp_setup_secret');

            // Inicia sesión al usuario.
            $remember = session('auth.remember');
            session()->forget(['auth.id', 'auth.remember', 'auth.2fa_passed']);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            Log::info('TOTP setup successful', [
                'event'     => 'AUTH_TOTP_SETUP_SUCCESS',
                'email'     => $user->email,
                'role'      => $user->role,
                'ip'        => $request->ip(),
                'timestamp' => now(),
            ]);
            AuditLog::record('totp_setup_success', 'Configuró TOTP correctamente', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        Log::warning('TOTP setup failed', [
            'event'     => 'AUTH_TOTP_SETUP_FAILED',
            'email'     => $user->email,
            'ip'        => $request->ip(),
            'timestamp' => now(),
        ]);
        AuditLog::record('totp_setup_failed', 'Falló la configuración TOTP', $request, [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->withErrors(['totp' => 'El código es incorrecto. Intenta de nuevo.']);
    }

    /**
     * Muestra el formulario de verificación TOTP para iniciar sesión.
     */
    public function show()
    {
        if (!session('auth.id') || !session('auth.2fa_passed')) {
            return redirect()->route('login');
        }

        $user = User::findOrFail(session('auth.id'));

        // Si el usuario aún no tiene secreto configurado, redirige a configuración.
        if (!$user->google2fa_secret) {
            return redirect()->route('totp.setup');
        }

        return view('auth.totp-verify');
    }

    /**
     * Verifica el código TOTP e inicia sesión.
     *
     * Una verificación exitosa completa el flujo admin y los intentos fallidos
     * se registran para visibilidad en auditoría.
     */
    public function verify(Request $request)
    {
        $request->validate(['totp' => 'required|digits:6']);

        $user = User::findOrFail(session('auth.id'));

        $google2fa = new Google2FA();
        $secret = decrypt($user->google2fa_secret);

        $valid = $google2fa->verifyKey($secret, $request->totp);

        if ($valid) {
            $remember = session('auth.remember');
            session()->forget(['auth.id', 'auth.remember', 'auth.2fa_passed']);

            Auth::login($user, $remember);
            $request->session()->regenerate();

            Log::info('TOTP verification successful', [
                'event'     => 'AUTH_TOTP_VERIFY_SUCCESS',
                'email'     => $user->email,
                'role'      => $user->role,
                'ip'        => $request->ip(),
                'timestamp' => now(),
            ]);
            AuditLog::record('login_success', 'Inició sesión correctamente con TOTP', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        Log::warning('TOTP verification failed', [
            'event'     => 'AUTH_TOTP_VERIFY_FAILED',
            'email'     => $user->email,
            'ip'        => $request->ip(),
            'timestamp' => now(),
        ]);
        AuditLog::record('totp_failed', 'Ingresó un código TOTP incorrecto', $request, [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return back()->withErrors(['totp' => 'El código es incorrecto. Intenta de nuevo.']);
    }
}
