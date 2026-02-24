<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\LabManagementController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ClassScheduleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================================================
// GUEST ROUTES (Belum Login)
// ============================================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::prefix('password')->group(function () {
        Route::get('/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify');
        Route::post('/verify-otp', [ForgotPasswordController::class, 'verify'])->name('password.verify.post');
        Route::get('/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');
        Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOtp'])->name('password.resend');
    });
});

// ============================================================================
// AUTHENTICATED ROUTES (Sudah Login)
// ============================================================================
Route::middleware('auth')->group(function () {

    // --- Logout ---
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/mahasiswa', [DashboardController::class, 'mahasiswa'])->name('dashboard.mahasiswa');
    Route::get('/dashboard/staff', [DashboardController::class, 'staff'])->name('dashboard.staff');

    // --- Profile Routes ---
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    // ========================================================================
    // 📋 BOOKING ROUTES
    // ========================================================================
    Route::middleware(['auth'])->prefix('booking')->name('booking.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/create', [BookingController::class, 'create'])->name('create');
        Route::get('/create-dosen', [BookingController::class, 'createDosen'])->name('create-dosen');
        Route::post('/store', [BookingController::class, 'store'])->name('store');
        Route::get('/search-users', [BookingController::class, 'searchUsers'])->name('search-users');
        
        // ✅ PRINT & PDF ROUTES
        Route::get('/{booking}/print', [BookingController::class, 'printForm'])->name('print');
        Route::get('/{booking}/print-form', [BookingController::class, 'printForm'])->name('print-form');
        Route::get('/{booking}/download-pdf', [BookingController::class, 'downloadPDF'])->name('download-pdf');
        
        // Approval routes (URUTAN PENTING: sebelum route {booking})
        Route::post('/{booking}/approve-dosen', [BookingController::class, 'approveByDosen'])->name('approve-dosen');
        Route::post('/{booking}/approve-teknisi', [BookingController::class, 'approveByTeknisi'])->name('approve-teknisi');
        Route::post('/{booking}/approve-kalab', [BookingController::class, 'approveByKalab'])->name('approve-kalab');
        
        // Other routes
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('/{booking}/reject', [BookingController::class, 'reject'])->name('reject');
        Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
    });

    // ========================================================================
    // 👨‍💼 ADMIN ROUTES (Hanya untuk role admin)
    // ========================================================================
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        // Admin Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [UserManagementController::class, 'create'])->name('create');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        });

        // Lab Management
        Route::prefix('labs')->name('labs.')->group(function () {
            Route::get('/', [LabManagementController::class, 'index'])->name('index');
            Route::get('/create', [LabManagementController::class, 'create'])->name('create');
            Route::post('/', [LabManagementController::class, 'store'])->name('store');
            Route::get('/{lab}', [LabManagementController::class, 'show'])->name('show');
            Route::get('/{lab}/edit', [LabManagementController::class, 'edit'])->name('edit');
            Route::put('/{lab}', [LabManagementController::class, 'update'])->name('update');
            Route::delete('/{lab}', [LabManagementController::class, 'destroy'])->name('destroy');
        });

        // Schedule Management
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::get('/calendar', [ScheduleController::class, 'calendar'])->name('calendar');
            Route::get('/available-slots', [ScheduleController::class, 'availableSlots'])->name('available-slots');
            Route::get('/{booking}', [ScheduleController::class, 'show'])->name('show');
            Route::post('/{booking}/update-status', [ScheduleController::class, 'updateStatus'])->name('update-status');
            Route::post('/{booking}/cancel', [ScheduleController::class, 'cancel'])->name('cancel');
        });

        // Class Schedule Management (Jadwal Kuliah)
        Route::prefix('class-schedules')->name('class-schedules.')->group(function () {
            Route::get('/', [ClassScheduleController::class, 'index'])->name('index');
            Route::get('/create', [ClassScheduleController::class, 'create'])->name('create');
            Route::post('/', [ClassScheduleController::class, 'store'])->name('store');
            Route::get('/{classSchedule}', [ClassScheduleController::class, 'show'])->name('show');
            Route::get('/{classSchedule}/edit', [ClassScheduleController::class, 'edit'])->name('edit');
            Route::put('/{classSchedule}', [ClassScheduleController::class, 'update'])->name('update');
            Route::delete('/{classSchedule}', [ClassScheduleController::class, 'destroy'])->name('destroy');
        });

    });

});

// ============================================================================
// FALLBACK & PUBLIC ROUTES
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