<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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

        // ====================================================================
        // 👨‍💼 ADMIN: Redirect ke Admin Dashboard (/admin/dashboard)
        // ====================================================================
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // ====================================================================
        // 🎓 MAHASISWA: Dashboard khusus mahasiswa
        // ====================================================================
        if ($user->isMahasiswa()) {
            return redirect()->route('dashboard.mahasiswa');
        }

        // ====================================================================
        // 👨‍🏫 DOSEN (termasuk yang juga Kalab)
        // ====================================================================
        if ($user->isDosen()) {
            // Jika Dosen juga Kalab, cek preferensi view mode
            if ($user->isKalab()) {
                $viewMode = session('dashboard_view_mode', 'schedule');
                if ($viewMode === 'management') {
                    return redirect()->route('booking.index');
                }
            }
            return redirect()->route('dashboard.staff');
        }

        // ====================================================================
        // 👔 KALAB (yang bukan dosen): Default ke management booking
        // ====================================================================
        if ($user->isKalab()) {
            return redirect()->route('booking.index');
        }

        // ====================================================================
        // 🔧 TEKNISI / 👨‍💼 STAFF / 👨‍💼 KETUA_LAB: Dashboard jadwal
        // ====================================================================
        if (in_array($user->role, ['teknisi', 'staff', 'ketua_lab'])) {
            return redirect()->route('dashboard.staff');
        }

        // Fallback
        return view('dashboard');
    }

    /**
     * Dashboard for Mahasiswa
     */
    public function mahasiswa()
    {
        if (!Auth::user()->isMahasiswa()) {
            abort(403, 'Unauthorized');
        }

        $selectedDate = request('date');
        return $this->getDashboardData('dashboard.mahasiswa', $selectedDate, false);
    }

    /**
     * Dashboard for Staff (Dosen, Teknisi, Ka Lab, Admin) - Dengan Stats + Grafik Analytics
     */
    public function staff(Request $request)
    {
        $user = Auth::user();
        $allowedRoles = ['dosen', 'ketua_lab', 'teknisi', 'staff', 'admin'];

        if (!in_array($user->role, $allowedRoles) && !$user->isKalab()) {
            abort(403, 'Unauthorized');
        }

        // ✅ Detect view mode untuk Kalab
        $isKalabView = $user->isKalab() && session('dashboard_view_mode', 'schedule') === 'management';
        $selectedDate = $request->get('date');

        // ========================================================================
        // ✅ STATS: Berdasarkan Role (Kalab = semua lab, Teknisi = lab sendiri)
        // ========================================================================
        $stats = [];
        $chartLabLabels = [];
        $chartLabData = [];
        $chartDayLabels = [];
        $chartDayData = [];
        $chartActivityLabels = [];
        $chartActivityData = [];
        $chartBorrowerLabels = [];
        $chartBorrowerData = [];
        $chartBorrowerRoles = [];

        // Scope query berdasarkan role
        $bookingQuery = Booking::query();
        $classQuery = ClassSchedule::query();
        $labScope = null;

        if ($user->isKalab() || $user->role === 'ketua_lab') {
            // 🔹 KALAB: Semua lab
            $labScope = null;
        } elseif ($user->isTeknisi() && !empty($user->lab_name)) {
            // 🔹 TEKNISI: Hanya lab sendiri
            $labScope = $user->lab_name;
            $bookingQuery->where('lab_name', $labScope);
            $classQuery->where('lab_name', $labScope);
        }

        // ------------------------------------------------------------------------
        // 📊 BASIC STATS
        // ------------------------------------------------------------------------
        $stats = [
            'total_labs' => $labScope ? 1 : Lab::where('status', 'active')->count(),
            'active_courses' => $classQuery->where('status', 'active')->count(),
            'bookings_today' => (clone $bookingQuery)->whereDate('booking_date', today())->count(),
            'bookings_this_month' => (clone $bookingQuery)
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->count(),
            'pending_count' => (clone $bookingQuery)->where('status', 'pending')->count(),
            'approved_dosen_count' => (clone $bookingQuery)->where('status', 'approved_dosen')->count(),
            'approved_teknisi_count' => (clone $bookingQuery)->where('status', 'approved_teknisi')->count(),
            'confirmed_count' => (clone $bookingQuery)->where('status', 'confirmed')->count(),
            'bookings_this_week' => (clone $bookingQuery)
                ->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'bookings_current_month' => (clone $bookingQuery)
                ->whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->count(),
            'bookings_last_month' => (clone $bookingQuery)
                ->whereMonth('booking_date', now()->subMonth()->month)
                ->whereYear('booking_date', now()->subMonth()->year)
                ->count(),
        ];

        // ------------------------------------------------------------------------
        // 📈 CHART 1: Lab Paling Sering Dipinjam (Bar Chart) - Last 30 days
        // ------------------------------------------------------------------------
        $labAnalytics = (clone $bookingQuery)
            ->select('lab_name', DB::raw('count(*) as total'))
            ->whereDate('booking_date', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->groupBy('lab_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $chartLabLabels = $labAnalytics->pluck('lab_name')->toArray();
        $chartLabData = $labAnalytics->pluck('total')->toArray();

        // ------------------------------------------------------------------------
        // 📈 CHART 2: Hari Paling Banyak Dipilih (Pie Chart) - All confirmed
        // ------------------------------------------------------------------------
        $dayAnalytics = (clone $bookingQuery)
            ->selectRaw('DAYNAME(booking_date) as day_name, COUNT(*) as total')
            ->where('status', 'confirmed')
            ->groupBy('day_name')
            ->orderByRaw('FIELD(day_name, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        $dayMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $chartDayLabels = $dayAnalytics->map(fn($d) => $dayMap[$d->day_name] ?? $d->day_name)->toArray();
        $chartDayData = $dayAnalytics->pluck('total')->toArray();

        // ------------------------------------------------------------------------
        // 📈 CHART 3: Jenis Kegiatan Peminjaman (Doughnut) - Last 3 months
        // ------------------------------------------------------------------------
        $activityAnalytics = (clone $bookingQuery)
            ->select('activity', DB::raw('count(*) as total'))
            ->where('status', 'confirmed')
            ->whereDate('booking_date', '>=', now()->subDays(90))
            ->groupBy('activity')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $chartActivityLabels = $activityAnalytics->pluck('activity')->toArray();
        $chartActivityData = $activityAnalytics->pluck('total')->toArray();

        // ------------------------------------------------------------------------
        // 📈 CHART 4: Top Peminjam (Horizontal Bar) - Last 3 months
        // ------------------------------------------------------------------------
        $topBorrowers = (clone $bookingQuery)
            ->select('user_id', DB::raw('count(*) as total'))
            ->where('status', 'confirmed')
            ->whereDate('booking_date', '>=', now()->subDays(90))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('user:id,name,role')
            ->get();

        $chartBorrowerLabels = $topBorrowers->map(fn($b) => $b->user?->name ?? 'Unknown')->toArray();
        $chartBorrowerData = $topBorrowers->pluck('total')->toArray();
        $chartBorrowerRoles = $topBorrowers->map(fn($b) => $b->user?->role ?? 'unknown')->toArray();

        // ========================================================================
        // ✅ SCHEDULE DATA
        // ========================================================================
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');

        $currentTime = Carbon::now('Asia/Jakarta');
        $today = $currentTime->toDateString();
        $todayName = $currentTime->isoFormat('dddd');
        $scheduleDate = $selectedDate ?? $today;
        $scheduleDayName = Carbon::parse($scheduleDate)->isoFormat('dddd');

        // Filter labs berdasarkan role
        if ($labScope) {
            $labs = [$labScope];
        } else {
            $labs = Lab::where('status', 'active')->orderBy('name')->pluck('name')->toArray();
            if (empty($labs)) {
                $labs = [
                    'Multimedia Cerdas (MMC)',
                    'Komputasi dan Sistem Jaringan (KSI)',
                    'Arsitektur dan Jaringan Komputer (AJK)',
                    'Mobile',
                    'Rekayasa Perangkat Lunak (RPL)',
                ];
            }
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
                    $scheduleDate, $scheduleDayName, $today, $todayName,
                    $isKalabView
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

        $realtimeDayName = $todayName;

        return view('dashboard.staff', compact(
            'scheduleData',
            'currentTime',
            'labs',
            'realtimeDayName',
            'scheduleDayName',
            'scheduleDate',
            'isKalabView',
            'stats',
            // ✅ Chart data for analytics
            'chartLabLabels', 'chartLabData',
            'chartDayLabels', 'chartDayData',
            'chartActivityLabels', 'chartActivityData',
            'chartBorrowerLabels', 'chartBorrowerData', 'chartBorrowerRoles'
        ));
    }

    /**
     * ✅ NEW: Toggle view mode untuk Kalab/Dosen+Kalab (AJAX/Form)
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
            'mode' => 'required|in:schedule,management',
        ]);

        session(['dashboard_view_mode' => $request->mode]);

        Log::info('Dashboard view mode changed', [
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'is_kalab' => $user->is_kalab,
            'new_mode' => $request->mode,
        ]);

        $redirectRoute = $request->mode === 'management'
            ? route('booking.index')
            : route('dashboard.staff');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'mode' => $request->mode,
                'redirect' => $redirectRoute,
                'message' => 'Tampilan dashboard berhasil diubah',
            ]);
        }

        return redirect($redirectRoute)
            ->with('success', 'Tampilan dashboard berhasil diubah');
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

    // ========================================================================
    // ✅ HELPER: Get session status untuk jadwal lab
    // ========================================================================
    private function getSessionStatusFromDb(
        $labName, $startTime, $endTime, $scheduleDate, $scheduleDayName,
        $today, $todayName, $isKalabView = false, $statusFilter = null
    ) {
        $currentTime = Carbon::now('Asia/Jakarta');
        $now = $currentTime->format('H:i');

        $scheduleDateObj = Carbon::parse($scheduleDate)->startOfDay();
        $todayObj = Carbon::parse($today)->startOfDay();

        $isToday = $scheduleDateObj->equalTo($todayObj);
        $isFutureDate = $scheduleDateObj->greaterThan($todayObj);
        $isPastDate = $scheduleDateObj->lessThan($todayObj);

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
                if ($isFutureDate) {
                    return [
                        'status' => 'terisi',
                        'label' => '📚 Terisi - Jadwal Kuliah',
                        'color' => 'red',
                        'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan,
                        'is_expired' => false,
                    ];
                }

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

        // ✅ 3. Default: Tersedia / Selesai / Masa Depan
        if ($isFutureDate) {
            return [
                'status' => 'tersedia',
                'label' => '✅ Tersedia',
                'color' => 'green',
                'info' => null,
                'is_expired' => false,
            ];
        }

        if ($isPastDate) {
            return [
                'status' => 'selesai',
                'label' => '⏹️ Selesai',
                'color' => 'gray',
                'info' => null,
                'is_expired' => false,
            ];
        }

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
        if (Carbon::parse($scheduleDate)->greaterThanOrEqualTo(Carbon::parse($today))) {
            return false;
        }

        $finalStatuses = ['confirmed', 'rejected', 'cancelled'];
        if (in_array($booking->status, $finalStatuses)) {
            return false;
        }

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
 * Public schedule page - Simple table view (no login required)
 */
public function publicSchedule(Request $request)
{
    $date = $request->get('date', date('Y-m-d'));
    $search = $request->get('search', '');
    $selectedLab = $request->get('lab', '');

    // Get all active labs
    $labs = Lab::where('status', 'active')->orderBy('name')->pluck('name')->toArray();

    // Get day name
    $dayName = Carbon::parse($date)->locale('id')->dayName;
    $dayMap = [
        'Senin' => ['Senin', 'Monday'], 'Selasa' => ['Selasa', 'Tuesday'],
        'Rabu' => ['Rabu', 'Wednesday'], 'Kamis' => ['Kamis', 'Thursday'],
        'Jumat' => ['Jumat', 'Friday'], 'Sabtu' => ['Sabtu', 'Saturday'],
        'Minggu' => ['Minggu', 'Sunday'],
    ];
    $possibleDays = $dayMap[$dayName] ?? [$dayName];

    // Get class schedules
    $classSchedules = ClassSchedule::whereIn('day', $possibleDays)
        ->where('status', 'active')
        ->orderBy('start_time')
        ->get();

    // Get confirmed bookings
    $bookings = Booking::whereDate('booking_date', $date)
        ->whereIn('status', ['confirmed', 'approved_teknisi', 'approved_kalab'])
        ->orderBy('start_time')
        ->get();

    // Build schedule data per lab
    $scheduleData = [];
    $sessions = [
        ['start' => '07:00', 'end' => '08:00', 'session' => 'Sesi 1'],
        ['start' => '08:00', 'end' => '09:00', 'session' => 'Sesi 2'],
        ['start' => '09:00', 'end' => '10:00', 'session' => 'Sesi 3'],
        ['start' => '10:00', 'end' => '11:00', 'session' => 'Sesi 4'],
        ['start' => '11:00', 'end' => '13:00', 'session' => 'Istirahat', 'is_break' => true],
        ['start' => '13:00', 'end' => '14:00', 'session' => 'Sesi 5'],
        ['start' => '14:00', 'end' => '15:00', 'session' => 'Sesi 6'],
        ['start' => '15:00', 'end' => '16:00', 'session' => 'Sesi 7'],
        ['start' => '16:00', 'end' => '17:00', 'session' => 'Sesi 8'],
    ];

    foreach ($labs as $labName) {
        if ($selectedLab && $selectedLab !== $labName) continue;

        $labSchedules = [];
        foreach ($sessions as $index => $session) {
            if ($session['is_break'] ?? false) {
                $labSchedules[] = [
                    'no' => $index + 1,
                    'session' => $session['session'],
                    'start' => $session['start'],
                    'end' => $session['end'],
                    'is_break' => true,
                    'status' => 'break',
                    'status_label' => 'Istirahat',
                    'status_color' => 'gray',
                ];
                continue;
            }

            $isBooked = false;
            $bookingInfo = '';
            $status = 'tersedia';
            $statusLabel = 'Tersedia';
            $statusColor = 'green';

            // Check class schedule conflict
            foreach ($classSchedules->where('lab_name', $labName) as $cs) {
                $csStart = substr($cs->start_time, 0, 5);
                $csEnd = substr($cs->end_time, 0, 5);
                if ($session['start'] < $csEnd && $session['end'] > $csStart) {
                    $isBooked = true;
                    $status = 'terisi';
                    $statusLabel = 'Kuliah';
                    $statusColor = 'red';
                    $bookingInfo = $cs->course_name . ' (Gol. ' . $cs->golongan . ')';
                    break;
                }
            }

            // Check booking conflict
            if (!$isBooked) {
                foreach ($bookings->where('lab_name', $labName) as $booking) {
                    $bStart = substr($booking->start_time ?? '', 0, 5);
                    $bEnd = substr($booking->end_time ?? '', 0, 5);
                    if (empty($bStart) || empty($bEnd)) continue;
                    if ($session['start'] < $bEnd && $session['end'] > $bStart) {
                        $isBooked = true;
                        $status = 'terisi';
                        $statusLabel = 'Dipinjam';
                        $statusColor = 'yellow';
                        $bookingInfo = $booking->activity;
                        break;
                    }
                }
            }

            $labSchedules[] = [
                'no' => $index + 1,
                'session' => $session['session'],
                'start' => $session['start'],
                'end' => $session['end'],
                'is_break' => false,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'booking_info' => $bookingInfo,
            ];
        }

        // Search filter
        if ($search) {
            $labSchedules = array_filter($labSchedules, function($item) use ($search) {
                return stripos($item['booking_info'] ?? '', $search) !== false ||
                       stripos($item['status_label'], $search) !== false;
            });
        }

        $scheduleData[$labName] = $labSchedules;
    }

    return view('public.schedule', compact(
        'labs', 'date', 'search', 'selectedLab', 'dayName', 'scheduleData'
    ));
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

        $viewMode = session('dashboard_view_mode', 'schedule');
        $isKalabView = $user->isKalab() && $viewMode === 'management';
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
            'view_mode' => $isKalabView ? 'management' : 'schedule',
            'schedule' => $scheduleData,
        ]);
    }
}
