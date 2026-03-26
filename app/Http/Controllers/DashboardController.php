<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Lab;
use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Main dashboard router based on user role AND view mode
     */
    public function index()
    {
        $user = Auth::user();

        // ✅ PERUBAHAN UTAMA: Kalab sekarang diarahkan ke "Kelola Booking" (booking.index)
        if ($user->isKalab()) {
            // Jika ingin ada toggle view, bisa pakai logika ini (Opsional):
            // $viewMode = session('dashboard_view_mode', 'kalab');
            //
            // if ($viewMode === 'schedule') {
            //     return redirect()->route('dashboard.staff'); // Redirect ke jadwal/slot
            // } else {
            //     return redirect()->route('booking.index'); // Default redirect ke daftar booking
            // }

            // Arahkan langsung ke halaman manajemen booking (Daftar Peminjaman)
            return redirect()->route('booking.index');
        }

        // User lain: redirect berdasarkan role biasa
        return match($user->role) {
            'mahasiswa' => redirect()->route('dashboard.mahasiswa'),
            'dosen', 'ketua_lab', 'teknisi', 'staff', 'admin' => redirect()->route('dashboard.staff'),
            default => view('dashboard'),
        };
    }

    /**
     * Dashboard for Mahasiswa
     */
    public function mahasiswa()
    {
        if (Auth::user()->role !== 'mahasiswa') {
            abort(403, 'Unauthorized');
        }

        $selectedDate = request('date');
        return $this->getDashboardData('dashboard.mahasiswa', $selectedDate, false);
    }

    /**
     * Dashboard for Staff (Dosen, Teknisi, Ka Lab, Admin) - Untuk Tampilan Jadwal
     */
    public function staff()
    {
        $user = Auth::user();
        $allowedRoles = ['dosen', 'ketua_lab', 'teknisi', 'staff', 'admin'];

        if (!in_array($user->role, $allowedRoles) && !$user->isKalab()) {
            abort(403, 'Unauthorized');
        }

        // ✅ Detect view mode untuk Kalab (Jika Kalab ingin melihat jadwal slot saja tanpa approve)
        $isKalabView = $user->isKalab() && session('dashboard_view_mode', 'kalab') === 'kalab';
        $selectedDate = request('date');

        return $this->getDashboardData('dashboard.staff', $selectedDate, $isKalabView);
    }

    /**
     * ✅ NEW: Toggle view mode untuk Kalab (AJAX/Form)
     */
    public function toggleViewMode(Request $request)
    {
        $user = Auth::user();

        if (!$user->isKalab()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'mode' => 'required|in:dosen,kalab,schedule',
        ]);

        // Simpan preferensi view Kalab
        session(['dashboard_view_mode' => $request->mode]);

        Log::info('Kalab view mode changed', [
            'user_id' => $user->id,
            'name' => $user->name,
            'new_mode' => $request->mode,
        ]);

        // Tentukan tujuan redirect berdasarkan mode
        $redirectRoute = match($request->mode) {
            'schedule' => route('dashboard.staff'), // Jadwal Slot (Kosong/Penuh)
            'kalab' => route('booking.index'),      // Daftar Booking (List Request)
            default => route('booking.index')
        };

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'mode' => $request->mode,
                'redirect' => $redirectRoute,
                'message' => 'Pengaturan tampilan berhasil diubah',
            ]);
        }

        return redirect($redirectRoute)
            ->with('success', 'Pengaturan tampilan berhasil diubah');
    }

    /**
     * ✅ Get dashboard data with Kalab view mode awareness
     */
    private function getDashboardData($viewName, $selectedDate = null, $isKalabView = false)
    {
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');

        $currentTime = Carbon::now('Asia/Jakarta');
        $today = $currentTime->toDateString();
        $todayName = $currentTime->isoFormat('dddd');

        $realtimeDayName = $todayName;
        $scheduleDate = $selectedDate ? Carbon::parse($selectedDate)->toDateString() : $today;
        $scheduleDayName = Carbon::parse($scheduleDate)->isoFormat('dddd');

        // ✅ KALAB VIEW: Tampilkan semua booking, Dosen biasa: hanya confirmed
        $bookingStatusFilter = $isKalabView
            ? ['pending', 'approved_dosen', 'approved_teknisi', 'confirmed']
            : ['confirmed'];

        $labs = Lab::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->pluck('name')
            ->toArray();

        if (empty($labs)) {
            $labs = [
                'Multimedia Cerdas (MMC)',
                'Komputasi dan Sistem Jaringan (KSI)',
                'Arsitektur dan Jaringan Komputer (AJK)',
                'Mobile',
                'Rekayasa Perangkat Lunak (RPL)',
            ];
        }

        $sessions = [
            ['start' => '07:00', 'end' => '08:00', 'name' => 'Sesi 1'],
            ['start' => '08:00', 'end' => '09:00', 'name' => 'Sesi 2'],
            ['start' => '09:00', 'end' => '10:00', 'name' => 'Sesi 3'],
            ['start' => '10:00', 'end' => '11:00', 'name' => 'Sesi 4'],
            ['start' => '11:00', 'end' => '13:00', 'name' => 'Istirahat', 'is_break' => true],
            ['start' => '13:00', 'end' => '14:00', 'name' => 'Sesi 5'],
            ['start' => '14:00', 'end' => '15:00', 'name' => 'Sesi 6'],
            ['start' => '15:00', 'end' => '16:00', 'name' => 'Sesi 7'],
            ['start' => '16:00', 'end' => '17:00', 'name' => 'Sesi 8'],
        ];

        $scheduleData = [];
        foreach ($labs as $lab) {
            $sessionsData = [];
            foreach ($sessions as $session) {
                $status = $this->getSessionStatusFromDb(
                    $lab,
                    $session['start'],
                    $session['end'],
                    $scheduleDate,
                    $scheduleDayName,
                    $today,
                    $todayName,
                    $isKalabView,
                    $bookingStatusFilter
                );

                $sessionsData[] = [
                    'no' => count($sessionsData) + 1,
                    'session' => $session['name'],
                    'start' => $session['start'],
                    'end' => $session['end'],
                    'status' => $status['status'],
                    'status_label' => $status['label'],
                    'status_color' => $status['color'],
                    'is_break' => $session['is_break'] ?? false,
                    'booking_info' => $status['info'] ?? null,
                    'booking_id' => $status['booking_id'] ?? null,
                    'is_expired' => $status['is_expired'] ?? false,
                    'is_kalab_view' => $isKalabView,
                ];
            }
            $scheduleData[$lab] = $sessionsData;
        }

        // ✅ Stats untuk Kalab view
        $stats = [];
        if ($isKalabView) {
            $stats = [
                'pending_count' => Booking::where('status', 'pending')->count(),
                'approved_dosen_count' => Booking::where('status', 'approved_dosen')->count(),
                'approved_teknisi_count' => Booking::where('status', 'approved_teknisi')->count(),
                'confirmed_count' => Booking::where('status', 'confirmed')->count(),
            ];
        }

        return view($viewName, compact(
            'scheduleData',
            'currentTime',
            'labs',
            'realtimeDayName',
            'scheduleDayName',
            'scheduleDate',
            'isKalabView',
            'stats'
        ));
    }

    /**
     * ✅ Get session status with Kalab view awareness
     */
    private function getSessionStatusFromDb(
        $labName, $startTime, $endTime, $scheduleDate, $scheduleDayName,
        $today, $todayName, $isKalabView = false, $statusFilter = null
    ) {
        $currentTime = Carbon::now('Asia/Jakarta');
        $now = $currentTime->format('H:i');
        $isToday = ($scheduleDate === $today);

        $dayMap = [
            'Senin' => ['Senin', 'Monday'],
            'Selasa' => ['Selasa', 'Tuesday'],
            'Rabu' => ['Rabu', 'Wednesday'],
            'Kamis' => ['Kamis', 'Thursday'],
            'Jumat' => ['Jumat', 'Friday'],
            'Sabtu' => ['Sabtu', 'Saturday'],
            'Minggu' => ['Minggu', 'Sunday'],
        ];

        $possibleDays = $dayMap[$scheduleDayName] ?? [$scheduleDayName];

        // ✅ 1. Check Class Schedule
        $classSchedules = ClassSchedule::where('lab_name', $labName)
            ->whereIn('day', $possibleDays)
            ->where('status', 'active')
            ->get();

        foreach ($classSchedules as $schedule) {
            $dbStart = substr($schedule->start_time, 0, 5);
            $dbEnd = substr($schedule->end_time, 0, 5);

            if ($startTime < $dbEnd && $endTime > $dbStart) {
                if ($isToday && $now >= $startTime && $now < $endTime) {
                    return [
                        'status' => 'proses',
                        'label' => '🎓 Kuliah Berlangsung',
                        'color' => 'yellow',
                        'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan,
                        'is_expired' => false,
                    ];
                }
                return [
                    'status' => 'terisi',
                    'label' => '📚 Terisi - Jadwal Kuliah',
                    'color' => 'red',
                    'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan,
                    'is_expired' => false,
                ];
            }
        }

        // ✅ 2. Check Bookings dengan filter sesuai view mode
        $bookingQuery = Booking::where('lab_name', $labName)
            ->whereDate('booking_date', $scheduleDate)
            ->with('user');

        if (!$isKalabView && !empty($statusFilter)) {
            $bookingQuery->whereIn('status', $statusFilter);
        }

        $bookings = $bookingQuery->get();

        foreach ($bookings as $booking) {
            $bStart = substr($booking->start_time ?? '', 0, 5);
            $bEnd = substr($booking->end_time ?? '', 0, 5);

            if (empty($bStart) || empty($bEnd)) continue;

            if ($startTime < $bEnd && $endTime > $bStart) {
                $isExpired = $this->isBookingExpired($booking, $scheduleDate, $today);

                if ($isExpired) {
                    return [
                        'status' => 'expired',
                        'label' => '⚠️ Expired - Belum Diproses',
                        'color' => 'gray',
                        'info' => ($booking->user->name ?? 'Unknown') . ' (Menunggu approval)',
                        'booking_id' => $booking->id,
                        'is_expired' => true,
                    ];
                }

                $statusMap = $this->getBookingStatusMap($booking, $isToday, $startTime, $endTime, $now, $isKalabView);

                if ($isToday && $now >= $startTime && $now < $endTime && $booking->status === 'confirmed') {
                    $statusMap['label'] = '🔄 Sedang Berlangsung';
                    $statusMap['color'] = 'yellow';
                    $statusMap['status'] = 'proses';
                }

                return [
                    'status' => $statusMap['status'],
                    'label' => $statusMap['label'],
                    'color' => $statusMap['color'],
                    'info' => $statusMap['info'],
                    'booking_id' => $booking->id,
                    'is_expired' => false,
                ];
            }
        }

        // ✅ 3. Default: Tersedia atau Selesai
        if ($now >= $startTime && $now < $endTime) {
            return [
                'status' => 'tersedia',
                'label' => '✅ Tersedia',
                'color' => 'green',
                'info' => null,
                'is_expired' => false,
            ];
        } elseif ($now < $startTime) {
            return [
                'status' => 'tersedia',
                'label' => '✅ Tersedia',
                'color' => 'green',
                'info' => null,
                'is_expired' => false,
            ];
        } else {
            return [
                'status' => 'selesai',
                'label' => '⏹️ Selesai',
                'color' => 'gray',
                'info' => null,
                'is_expired' => false,
            ];
        }
    }

    /**
     * ✅ HELPER: Cek apakah booking sudah expired
     */
    private function isBookingExpired($booking, $scheduleDate, $today): bool
    {
        if ($scheduleDate >= $today) return false;
        $finalStatuses = ['confirmed', 'rejected', 'cancelled'];
        if (in_array($booking->status, $finalStatuses)) return false;
        return true;
    }

    /**
     * ✅ HELPER: Mapping status booking ke label, warna, dan info
     */
    private function getBookingStatusMap($booking, $isToday, $startTime, $endTime, $now, $isKalabView = false): array
    {
        $userName = $booking->user->name ?? 'Unknown';
        $purpose = Str::limit($booking->purpose ?? '', 30);

        return match($booking->status) {
            'pending' => [
                'status' => 'pending',
                'label' => $isKalabView ? '⏳ Menunggu Approval Dosen' : '🔒 Terisi (Pending)',
                'color' => $isKalabView ? 'orange' : 'gray',
                'info' => "{$userName}" . ($isKalabView ? " - {$purpose}" : ""),
            ],
            'approved_dosen' => [
                'status' => 'approved_dosen',
                'label' => $isKalabView ? '✅ Disetujui Dosen' : '🔒 Terisi (Approved)',
                'color' => $isKalabView ? 'blue' : 'gray',
                'info' => "{$userName}" . ($isKalabView ? " - {$purpose}" : ""),
            ],
            'approved_teknisi' => [
                'status' => 'approved_teknisi',
                'label' => $isKalabView ? '✅ Menunggu Approval Kalab' : '🔒 Terisi (Approved)',
                'color' => $isKalabView ? 'indigo' : 'gray',
                'info' => "{$userName}" . ($isKalabView ? " - {$purpose}" : ""),
            ],
            'confirmed' => [
                'status' => 'terisi',
                'label' => '🔒 Terisi - Booking Confirmed',
                'color' => 'red',
                'info' => "{$userName} - {$purpose}",
            ],
            'rejected' => [
                'status' => 'rejected',
                'label' => '❌ Ditolak',
                'color' => 'gray',
                'info' => "{$userName}" . ($booking->rejection_reason ? " ({$booking->rejection_reason})" : ""),
            ],
            'cancelled' => [
                'status' => 'cancelled',
                'label' => '🗑️ Dibatalkan',
                'color' => 'gray',
                'info' => "{$userName}",
            ],
            default => [
                'status' => 'unknown',
                'label' => '❓ ' . ucfirst($booking->status ?? 'Unknown'),
                'color' => 'gray',
                'info' => "{$userName}",
            ],
        };
    }

    /**
     * ✅ API endpoint untuk filter jadwal via kalender (AJAX)
     */
    public function getScheduleByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'lab' => 'nullable|string',
        ]);

        $user = Auth::user();
        $selectedDate = Carbon::parse($request->date)->toDateString();
        $scheduleDayName = Carbon::parse($selectedDate)->isoFormat('dddd');
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $todayName = Carbon::now('Asia/Jakarta')->isoFormat('dddd');

        // Filter booking status berdasarkan role user
        $isKalabView = $user->isKalab() && session('dashboard_view_mode', 'kalab') === 'kalab';
        $bookingStatusFilter = $isKalabView
            ? ['pending', 'approved_dosen', 'approved_teknisi', 'confirmed']
            : ['confirmed'];

        $labs = $request->filled('lab')
            ? [$request->lab]
            : Lab::where('status', 'active')->orderBy('name')->pluck('name')->toArray();

        if (empty($labs)) {
            $labs = ['Multimedia Cerdas (MMC)', 'Komputasi dan Sistem Jaringan (KSI)'];
        }

        $sessions = [
            ['start' => '07:00', 'end' => '08:00', 'name' => 'Sesi 1'],
            ['start' => '08:00', 'end' => '09:00', 'name' => 'Sesi 2'],
            ['start' => '09:00', 'end' => '10:00', 'name' => 'Sesi 3'],
            ['start' => '10:00', 'end' => '11:00', 'name' => 'Sesi 4'],
            ['start' => '11:00', 'end' => '13:00', 'name' => 'Istirahat', 'is_break' => true],
            ['start' => '13:00', 'end' => '14:00', 'name' => 'Sesi 5'],
            ['start' => '14:00', 'end' => '15:00', 'name' => 'Sesi 6'],
            ['start' => '15:00', 'end' => '16:00', 'name' => 'Sesi 7'],
            ['start' => '16:00', 'end' => '17:00', 'name' => 'Sesi 8'],
        ];

        $scheduleData = [];
        foreach ($labs as $lab) {
            $sessionsData = [];
            foreach ($sessions as $session) {
                $status = $this->getSessionStatusFromDb(
                    $lab, $session['start'], $session['end'],
                    $selectedDate, $scheduleDayName, $today, $todayName,
                    $isKalabView, $bookingStatusFilter
                );

                $sessionsData[] = [
                    'session' => $session['name'],
                    'start' => $session['start'],
                    'end' => $session['end'],
                    'status' => $status['status'],
                    'status_label' => $status['label'],
                    'status_color' => $status['color'],
                    'is_break' => $session['is_break'] ?? false,
                    'booking_info' => $status['info'] ?? null,
                    'booking_id' => $status['booking_id'] ?? null,
                    'is_expired' => $status['is_expired'] ?? false,
                ];
            }
            $scheduleData[$lab] = $sessionsData;
        }

        return response()->json([
            'success' => true,
            'date' => $selectedDate,
            'day_name' => $scheduleDayName,
            'view_mode' => $isKalabView ? 'kalab' : 'dosen',
            'schedule' => $scheduleData,
        ]);
    }
}
