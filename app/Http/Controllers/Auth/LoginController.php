<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Attempt login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect based on role
            return redirect()->intended($this->getRedirectPath($user));
        }

        // Login failed
        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Get redirect path based on user role
     */
    protected function getRedirectPath($user)
    {
        return match($user->role) {
            'mahasiswa' => '/dashboard',
            'dosen', 'ketua_lab', 'teknisi', 'staff' => '/dashboard/staff',
            'admin' => '/dashboard',
            default => '/dashboard',
        };
    }
}
