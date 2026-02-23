<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Cek role berdasarkan domain email
        $email = $credentials['email'];
        $domain = substr(strrchr($email, "@"), 1);

        $role = $this->determineRole($domain);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Redirect berdasarkan role
            return redirect()->intended($this->getRedirectPath($role));
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Determine user role based on email domain
     */
    private function determineRole($domain)
    {
        if ($domain === 'student.polije.ac.id') {
            return 'mahasiswa';
        } elseif ($domain === 'polije.ac.id') {
            // Perlu cek database untuk menentukan apakah dosen, ketua lab, atau teknisi
            return 'staff'; // default untuk polije.ac.id
        }

        return 'unknown';
    }

    /**
     * Get redirect path based on role
     */
    private function getRedirectPath($role)
    {
        return match($role) {
            'mahasiswa' => '/dashboard/mahasiswa',
            'staff' => '/dashboard/staff',
            default => '/dashboard',
        };
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
