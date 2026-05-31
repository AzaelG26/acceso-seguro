<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use \Illuminate\Support\Facades\URL;

class OtpController extends Controller
{
    /**
     *
     */
    public function show () {
        if (!session('auth.id')){
            return redirect()->route('login');
        }
        return view('auth.otp');
    }

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

    public function verify(Request $request)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $user = User::findOrFail(session('auth.id'));

        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'El código expiró.']);
        }

        if (!password_verify($request->otp, $user->otp_code)) {
            Log::warning('OTP fallido', ['email' => $user->email, 'ip' => $request->ip()]);
            return back()->withErrors(['otp' => 'Código incorrecto.']);
        }

        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        if($user->IsAdmin()){
            if($user->last_known_ip && $user->last_known_ip !== $request->ip()){
                $approveUrl = URL::signedRoute('ip.approve',[
                    'user' => $user->id,
                    'ip'   => $request->ip(),
                ]);

                $blockUrl = URL::signedRoute('ip.block', [
                    'user' => $user->id,
                ]);

                Mail::raw(
                    "Se detectó un inicio de sesión desde una IP desconocida.\n\n" .
                    "IP: {$request->ip()}\n" .
                    "Hora: " . now() . "\n\n" .
                    "¿Fuiste tú?\n" .
                    "Aprobar acceso: $approveUrl\n\n" .
                    "No fui yo: $blockUrl",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Inicio de sesión desde IP desconocida');
                    }
                );


                Log::warning('Admin login desde IP desconocida', [
                    'email'       => $user->email,
                    'ip_conocida' => $user->last_known_ip,
                    'ip_actual'   => $request->ip(),
                ]);
                // return back()->withErrors(['otp' => 'Acceso denegado. IP no reconocida.']);

                session()->forget(['auth.id', 'auth.remember']);

                return redirect()->route('login')->with('status', 'IP desconocida detectada. Revisa tu correo para aprobar el acceso.');

            }
        }

        $remember = session('auth.remember');
        session()->forget(['auth.id', 'auth.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->update(['last_known_ip' => $request->ip()]);

        Log::info('OTP exitoso', ['email' => $user->email, 'ip' => $request->ip()]);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function approveIp(Request $request)
    {
        $user = User::findOrFail($request->user);
        $user->update(['last_known_ip' => $request->ip]);

        Log::info('IP aprobada por admin', [
            'email' => $user->email,
            'ip'    => $request->ip,
        ]);

        return redirect()->route('login')->with('status', 'IP aprobada. Ya puedes iniciar sesión desde esta ubicación.');
    }

    public function blockIp(Request $request)
    {
        $user = User::findOrFail($request->user);

        Log::critical('Acceso no autorizado rechazado', [
            'email'      => $user->email,
            'ip_intruso' => $request->ip,
        ]);

        return redirect()->route('login')->with('status', 'Acceso rechazado.');
    }

}
