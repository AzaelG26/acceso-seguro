<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    /**
     * Muestra el formulario de verificación OTP (Segundo factor por correo).
     *
     * Si no hay una sesión de autenticación parcial en progreso, redirige al login
     * para evitar accesos directos no autorizados.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Retorna la vista o redirecciona al login
     */
    public function show () {
        if (!session('auth.id')){
            return redirect()->route('login');
        }
        return view('auth.otp');
    }

    /**
     * Genera y envía un código OTP de 6 dígitos al correo del usuario.
     *
     * El código se guarda cifrado con bcrypt en la base de datos para prevenir 
     * su robo directo y expira exactamente en 10 minutos por razones de seguridad.
     *
     * @param \App\Models\User $user Modelo del usuario al que se le enviará el OTP
     * @return void
     * @throws \Exception Si falla la generación del número aleatorio
     */
    public function send(User $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => bcrypt($code),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw("Tu código de verificación es: $code\nExpira en 10 minutos.", function ($message) use ($user) {
            $message->to($user->email)->subject('Código de verificación');
        });
    }

    /**
     * Verifica el código OTP ingresado por el usuario.
     *
     * Valida formato, comprueba que no esté expirado y verifica el hash.
     * Si el usuario es administrador, lo pasa a la fase TOTP.
     * Si es usuario normal, finaliza el login y regenera la sesión.
     * Invalida el OTP después de usarse.
     *
     * @param  \Illuminate\Http\Request  $request Petición HTTP con el código OTP
     * @return \Illuminate\Http\RedirectResponse Redirección al Dashboard o al paso TOTP
     * @throws \Illuminate\Validation\ValidationException Si el formato del OTP es incorrecto
     */
    public function verify(Request $request)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $user = User::findOrFail(session('auth.id'));

        // Verifica la expiración del OTP.
        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            AuditLog::record('otp_expired', 'Intentó usar un código OTP expirado', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return back()->withErrors(['otp' => 'El código expiró.']);
        }

        // Verifica que el código sea correcto.
        if (!password_verify($request->otp, $user->otp_code)) {
            Log::warning('OTP fallido', ['email' => $user->email, 'ip' => $request->ip()]);
            AuditLog::record('otp_failed', 'Ingresó un código OTP incorrecto', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return back()->withErrors(['otp' => 'Código incorrecto.']);
        }

        // Invalida el OTP después de usarlo una sola vez.
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        // Tercer factor para admin: TOTP con Google Authenticator.
        if ($user->isAdmin()) {
            // Marca que el 2FA por correo fue aprobado.
            session(['auth.2fa_passed' => true]);

            AuditLog::record('otp_success', 'Admin validó OTP y pasó al flujo TOTP', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Redirige al flujo de 3FA con TOTP.
            return redirect()->route('totp.show');
        }

        $remember = session('auth.remember');
        session()->forget(['auth.id', 'auth.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Actualiza la IP conocida del usuario.
        $user->update(['last_known_ip' => $request->ip()]);

        Log::info('OTP exitoso', ['email' => $user->email, 'ip' => $request->ip()]);
        AuditLog::record('login_success', 'Inició sesión correctamente con OTP', $request, [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

}
