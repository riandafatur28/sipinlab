<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        // Set session dulu sebelum kirim email, agar middleware verify tidak gagal
        session([
            'reset_otp'      => hash('sha256', $otp),
            'reset_otp_plain' => $otp,
            'reset_email'    => $user->email,
            'reset_otp_time' => now(),
            'reset_attempts' => 0,
        ]);

        $gmail = app(GmailService::class);
        $html  = $this->buildOtpEmail($otp, $user->name);

        $sent = $gmail->send($user->email, $user->name, '[SipinLab] Kode OTP Reset Password', $html);

        if (!$sent) {
            Log::error('Failed to send OTP email via Gmail API', ['email' => $user->email]);
            return back()->withErrors(['username' => 'Gagal mengirim email. Periksa konfigurasi Gmail API.']);
        }

        Log::info('OTP email sent via Gmail API', ['email' => $user->email]);

        return redirect()->route('password.verify')
            ->with('status', 'Kode OTP telah dikirim ke email ' . $user->email . '. Cek inbox kamu.');
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
                Rules\Password::min(8),
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

        session([
            'reset_otp'        => hash('sha256', $otp),
            'reset_otp_plain'  => $otp,
            'reset_otp_time'   => now(),
            'reset_last_resend' => now(),
            'reset_attempts'   => 0,
        ]);

        $gmail = app(GmailService::class);
        $html  = $this->buildOtpEmail($otp, $user->name);
        $sent  = $gmail->send($user->email, $user->name, '[SipinLab] Kode OTP Reset Password', $html);

        if (!$sent) {
            Log::error('Failed to resend OTP via Gmail API', ['email' => $email]);
            return response()->json(['status' => false, 'message' => 'Gagal mengirim email. Coba lagi.']);
        }

        Log::info('OTP email resent via Gmail API', ['email' => $email]);
        return response()->json(['status' => true, 'message' => 'Kode OTP baru telah dikirim ke email Anda.']);
    }

    private function buildOtpEmail(string $otp, string $name): string
    {
        return "
        <div style='font-family:Arial,sans-serif;max-width:480px;margin:auto;padding:24px;border:1px solid #e2e8f0;border-radius:12px'>
            <h2 style='color:#1e40af;margin-bottom:8px'>🔐 Reset Password SipinLab</h2>
            <p style='color:#374151'>Halo <strong>{$name}</strong>,</p>
            <p style='color:#374151'>Gunakan kode OTP berikut untuk mereset password kamu:</p>
            <div style='background:#eff6ff;border:2px dashed #3b82f6;border-radius:8px;padding:20px;text-align:center;margin:20px 0'>
                <span style='font-size:36px;font-weight:bold;letter-spacing:12px;color:#1d4ed8'>{$otp}</span>
            </div>
            <p style='color:#6b7280;font-size:13px'>⏱️ Kode berlaku <strong>5 menit</strong> dan hanya bisa digunakan sekali.</p>
            <p style='color:#6b7280;font-size:13px'>Jika kamu tidak meminta reset password, abaikan email ini.</p>
            <hr style='border:none;border-top:1px solid #e2e8f0;margin:20px 0'>
            <p style='color:#9ca3af;font-size:12px;text-align:center'>SipinLab — Sistem Peminjaman Laboratorium<br>Politeknik Negeri Jember</p>
        </div>";
    }
}
