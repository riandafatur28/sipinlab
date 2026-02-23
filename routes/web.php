<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
// HAPUS/HAPUS baris ini:
// use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;

// ============================================================================
// GUEST ROUTES (Belum Login)
// ============================================================================
Route::middleware('guest')->group(function () {

    // --- Regular Login ---
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // ❌ HAPUS Google SSO routes ini:
    // Route::prefix('auth')->group(function () {
    //     Route::get('/google', [GoogleAuthController::class, 'redirectToGoogle'])
    //         ->name('auth.google');
    //     Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])
    //         ->name('auth.google.callback');
    // });

    // --- Forgot Password Flow ---
    Route::prefix('password')->group(function () {
        Route::get('/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('password.request');
        Route::post('/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('password.email');

        Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])
            ->name('password.verify');
        Route::post('/verify-otp', [ForgotPasswordController::class, 'verify'])
            ->name('password.verify.post');

        Route::get('/reset', [ForgotPasswordController::class, 'showResetForm'])
            ->name('password.reset');
        Route::post('/reset', [ForgotPasswordController::class, 'reset'])
            ->name('password.update');

        Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOtp'])
            ->name('password.resend');
    });
});

// ============================================================================
// AUTHENTICATED ROUTES (Sudah Login)
// ============================================================================
Route::middleware('auth')->group(function () {

    // --- Logout ---
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // // --- Dashboard ---
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/dashboard/mahasiswa', [DashboardController::class, 'mahasiswa'])
    //     ->name('dashboard.mahasiswa');
    // Route::get('/dashboard/staff', [DashboardController::class, 'staff'])
    //     ->name('dashboard.staff');

    // --- Profile Routes ---
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [App\Http\Controllers\ProfileController::class, 'update'])->name('update');
    });
});

// ============================================================================
// FALLBACK & HEALTH CHECK
// ============================================================================

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => config('app.env'),
        'timestamp' => now(),
    ]);
});
