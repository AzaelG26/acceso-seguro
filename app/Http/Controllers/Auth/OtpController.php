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
     *  Show the OTP verification form
     *  If there is no authentication session in progress, redirect to login.
     */
    public function show () {
        if (!session('auth.id')){
            return redirect()->route('login');
        }
        return view('auth.otp');
    }

    /**
     * Generate and send a 6-digit OTP code to the user's email.
     * The code is stored hashed with bcrypt and expires in 10 minutes.
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
     * Verify the OTP code entered by the user.
     *
     * Flow:
     * 1. Validate that the code has 6 digits.
     * 2. Verify that it has not expired.
     * 3. Compare the code against the stored hash.
     * 4. For admin: verify if the IP is known.
     *    - Unknown IP → send email with signed links to approve/reject.
     *    - Known IP → direct login.
     * 5. For user: direct login after successful OTP.
     */
    public function verify(Request $request)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $user = User::findOrFail(session('auth.id'));

        // Check OTP expiration
        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            AuditLog::record('otp_expired', 'Intentó usar un código OTP expirado', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return back()->withErrors(['otp' => 'El código expiró.']);
        }

        // Verify the code is correct
        if (!password_verify($request->otp, $user->otp_code)) {
            Log::warning('OTP fallido', ['email' => $user->email, 'ip' => $request->ip()]);
            AuditLog::record('otp_failed', 'Ingresó un código OTP incorrecto', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return back()->withErrors(['otp' => 'Código incorrecto.']);
        }

        // Invalidate the OTP after use (one-time use)
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        // Third factor for admin: TOTP (Google Authenticator)
        if ($user->isAdmin()) {
            // Mark that the 2FA (Email OTP) has been passed
            session(['auth.2fa_passed' => true]);

            AuditLog::record('otp_success', 'Admin validó OTP y pasó al flujo TOTP', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Redirect to the 3FA flow (TOTP)
            return redirect()->route('totp.show');
        }

        $remember = session('auth.remember');
        session()->forget(['auth.id', 'auth.remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Update the user's known IP
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
