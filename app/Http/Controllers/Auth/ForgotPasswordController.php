<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Validation\Rules;

class ForgotPasswordController extends Controller
{
    protected $allowedDomains = [
        'student.polije.ac.id',
        'polije.ac.id'
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!session('reset_email')) {
                return redirect()->route('password.request')
                    ->withErrors(['session' => 'Sesi tidak valid. Silakan request ulang.']);
            }
            return $next($request);
        })->only(['showVerifyForm', 'verify', 'showResetForm', 'reset', 'resendOtp']);
    }

    public function showLinkRequestForm()
    {
        session()->forget(['reset_otp', 'reset_email', 'reset_otp_time', 'debug_otp', 'reset_attempts']);
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $domain = substr(strrchr($value, "@"), 1);
                    if (!in_array($domain, $this->allowedDomains)) {
                        $fail('Email harus menggunakan domain @student.polije.ac.id atau @polije.ac.id');
                    }
                }
            ],
        ]);

        $user = User::where('email', $request->username)->first();

        if (!$user) {
            Log::warning('Forgot password attempt with non-existent email', ['email' => $request->username]);
            return back()->withErrors(['username' => 'Jika email terdaftar, kode OTP akan dikirim.']);
        }

        $domain = substr(strrchr($user->email, "@"), 1);
        if (!in_array($domain, $this->allowedDomains)) {
            Log::warning('Forgot password attempt with invalid domain', ['email' => $user->email, 'domain' => $domain]);
            return back()->withErrors(['username' => 'Email tidak terdaftar dalam sistem Polije.']);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Mail::to($user->email)->send(new OtpMail($otp, 5, $user->name));
            Log::info('OTP email sent successfully', ['email' => $user->email, 'domain' => $domain]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', ['email' => $user->email, 'error' => $e->getMessage()]);

            if (app()->environment('local', 'development')) {
                session(['debug_otp' => $otp]);
                return redirect()->route('password.verify')
                    ->with('status', 'Development Mode - OTP: ' . $otp);
            }

            return back()->withErrors(['username' => 'Gagal mengirim email. Silakan coba lagi atau hubungi admin.']);
        }

        session([
            'reset_otp' => hash('sha256', $otp),
            'reset_otp_plain' => $otp,
            'reset_email' => $user->email,
            'reset_otp_time' => now(),
            'reset_attempts' => 0,
        ]);

        return redirect()->route('password.verify')
            ->with('status', 'Kode OTP telah dikirim ke email ' . $domain . ' Anda.');
    }

    public function showVerifyForm()
    {
        return view('auth.verify-otp');
    }

    public function verify(Request $request)
{
    $request->validate([
        'otp' => 'required|string|min:6|max:6',
    ]);

    // Rate limiting
    $attempts = session('reset_attempts', 0);
    if ($attempts >= 5) {
        session()->forget(['reset_otp', 'reset_email', 'reset_otp_time', 'debug_otp']);
        return back()->withErrors(['otp' => 'Terlalu banyak percobaan. Silakan request kode OTP baru.']);
    }

    session(['reset_attempts' => $attempts + 1]);

    // Cek OTP - coba 2 cara (hash dan plain)
    $inputOtp = $request->otp;
    $storedOtpHash = session('reset_otp');
    $storedOtpPlain = session('reset_otp_plain');

    // Debug log
    Log::info('OTP Verification', [
        'input' => $inputOtp,
        'stored_hash' => $storedOtpHash,
        'stored_plain' => $storedOtpPlain,
        'input_hash' => hash('sha256', $inputOtp),
    ]);

    // Bandingkan dengan hash
    $inputOtpHash = hash('sha256', $inputOtp);
    $hashMatch = hash_equals($storedOtpHash ?? '', $inputOtpHash);

    // Atau bandingkan plain (fallback)
    $plainMatch = ($inputOtp === $storedOtpPlain);

    if (!$hashMatch && !$plainMatch) {
        return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
    }

    // Cek expired
    $otpTime = session('reset_otp_time');
    if (!$otpTime || now()->diffInMinutes($otpTime) > 5) {
        return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan request ulang.']);
    }

    // Reset attempts
    session(['reset_attempts' => 0]);

    return redirect()->route('password.reset');
}

    public function showResetForm()
    {
        return view('auth.reset-password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
                    ->min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $email = session('reset_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::error('Password reset failed: user not found', ['email' => $email]);
            session()->forget(['reset_otp', 'reset_email', 'reset_otp_time', 'debug_otp']);
            return redirect()->route('password.request')
                ->withErrors(['system' => 'Terjadi kesalahan. Silakan coba lagi.']);
        }

        $user->password = Hash::make($request->password);
        $user->remember_token = null;
        $user->save();

        session()->forget(['reset_otp', 'reset_otp_plain', 'reset_email', 'reset_otp_time', 'debug_otp', 'reset_attempts']);
        Log::info('Password successfully reset', ['email' => $email]);

        return redirect()->route('login')
            ->with('status', 'Password berhasil direset. Silakan login dengan password baru.');
    }

    public function resendOtp(Request $request)
    {
        $email = session('reset_email');

        if (!$email) {
            return response()->json(['status' => false, 'message' => 'Sesi tidak valid'], 400);
        }

        $domain = substr(strrchr($email, "@"), 1);
        if (!in_array($domain, $this->allowedDomains)) {
            return response()->json(['status' => false, 'message' => 'Domain email tidak valid'], 403);
        }

        $lastResend = session('reset_last_resend');
        if ($lastResend && now()->diffInSeconds($lastResend) < 60) {
            $remaining = 60 - now()->diffInSeconds($lastResend);
            return response()->json([
                'status' => false,
                'message' => "Silakan tunggu {$remaining} detik sebelum mengirim ulang."
            ], 429);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Mail::to($user->email)->send(new OtpMail($otp, 5, $user->name));
            Log::info('OTP email resent successfully', ['email' => $email, 'domain' => $domain]);
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP email', ['email' => $email, 'error' => $e->getMessage()]);

            if (app()->environment('local', 'development')) {
                session(['debug_otp' => $otp]);
                return response()->json(['status' => true, 'message' => 'Development Mode - OTP baru: ' . $otp]);
            }

            return response()->json(['status' => false, 'message' => 'Gagal mengirim email.']);
        }

        session([
            'reset_otp' => hash('sha256', $otp),
            'reset_otp_plain' => $otp,
            'reset_otp_time' => now(),
            'reset_last_resend' => now(),
            'reset_attempts' => 0,
        ]);

        return response()->json(['status' => true, 'message' => 'Kode OTP baru telah dikirim ke email Anda.']);
    }
}
