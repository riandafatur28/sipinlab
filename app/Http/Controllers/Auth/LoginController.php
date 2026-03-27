<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended($this->getRedirectPath(Auth::user()));
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // ✅ SET DEFAULT VIEW MODE UNTUK KALAB
            if ($user->isKalab()) {
                session(['dashboard_view_mode' => 'kalab']);
            } else {
                session()->forget('dashboard_view_mode');
            }

            Log::info('User logged in', [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_kalab' => $user->is_kalab ?? false,
                'is_kalab_method' => $user->isKalab(),
                'view_mode' => session('dashboard_view_mode'),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->intended($this->getRedirectPath($user));
        }

        Log::warning('Login attempt failed', [
            'email' => $request->email,
            'ip_address' => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Logout
     */
   public function logout(Request $request)
{
    // Destroy session
    Auth::logout();

    // Invalidate session
    $request->session()->invalidate();

    // Regenerate CSRF token
    $request->session()->regenerateToken();

    // Redirect to login with message
    return redirect()->route('login')
        ->with('success', '✅ Anda telah berhasil logout.');
}
    /**
     * Get redirect path based on user role AND kalab status
     */
    protected function getRedirectPath($user)
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($user->isKalab()) {
            return route('dashboard.staff');
        }

        if ($user->role === 'ketua_lab') {
            return route('dashboard.staff');
        }

        if ($user->isDosen() || $user->isTeknisi() || $user->role === 'staff') {
            return route('dashboard.staff');
        }

        if ($user->isMahasiswa()) {
            return route('dashboard.mahasiswa');
        }

        return route('dashboard');
    }



    /**
     * Helper: Check if user should see Kalab view
     */
    protected function shouldShowKalabView($user): bool
    {
        return $user->isKalab() || $user->role === 'ketua_lab';
    }

    /**
     * Get dashboard config based on role/kalab status
     */
    public function getDashboardConfig($user)
    {
        return [
            'redirect' => $this->getRedirectPath($user),
            'is_kalab' => $user->isKalab(),
            'can_approve' => $user->canApproveBookings(),
            'can_view_all' => $user->canViewAllBookings(),
            'menu_type' => $this->getMenuType($user),
            'view_mode' => $user->isKalab() ? session('dashboard_view_mode', 'kalab') : null,
        ];
    }

    /**
     * Determine menu type for sidebar rendering
     */
    protected function getMenuType($user): string
    {
        if ($user->isAdmin()) return 'admin';
        if ($user->isKalab() || $user->role === 'ketua_lab') return 'kalab';
        if ($user->isDosen()) return 'dosen';
        if ($user->isTeknisi()) return 'teknisi';
        if ($user->isMahasiswa()) return 'mahasiswa';
        return 'default';
    }
}
