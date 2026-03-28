<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // ====================================================================
        // ✅ ALLOWED ROLES: Admin, Kalab/Ketua Lab, ATAU Teknisi
        // ====================================================================
        // - Admin: Akses penuh ke semua fitur & data
        // - Kalab/Ketua Lab: Akses management dengan scope lab sendiri (jika ada)
        // - Teknisi: Akses management booking dengan scope lab sendiri (wajib punya lab_name)
        // ====================================================================

        if ($user->isAdmin() || $user->isKalab() || $user->role === 'ketua_lab' || $user->isTeknisi()) {
            return $next($request);
        }

        // ====================================================================
        // ❌ UNAUTHORIZED: Redirect atau return JSON error
        // ====================================================================

        // Handle AJAX/JSON requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Anda tidak memiliki akses ke resource ini.'
            ], 403);
        }

        // Handle regular requests - redirect to dashboard with error message
        return redirect()->route('dashboard')
            ->with('error', '❌ Anda tidak memiliki akses ke halaman ini.');
    }
}
