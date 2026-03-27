<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lab;
use App\Models\Booking;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Display dashboard admin dengan stats lengkap + jadwal real-time + analytics
     */
    public function index(Request $request)
    {
        // ====================================================================
        // ✅ STATS: Users, Labs, Courses, Bookings Real-time
        // ====================================================================
        $stats = [
            // Users Stats
            'total_users' => User::count(),
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'dosen' => User::where('role', 'dosen')->count(),
            'ketua_lab' => User::where('role', 'ketua_lab')->orWhere('is_kalab', true)->count(),
            'teknisi' => User::where('role', 'teknisi')->count(),
            'admin' => User::where('role', 'admin')->count(),

            // ✅ NEW: Labs & Courses Stats
            'total_labs' => Lab::where('status', 'active')->count(),
            'active_courses' => ClassSchedule::where('status', 'active')->count(),

            // ✅ NEW: Real-time Booking Stats
            'bookings_today' => Booking::whereDate('booking_date', today())->count(),
            'bookings_this_month' => Booking::whereMonth('booking_date', now()->month)
                ->whereYear('booking_date', now()->year)
                ->count(),
            'bookings_confirmed_today' => Booking::whereDate('booking_date', today())
                ->where('status', 'confirmed')
                ->count(),
            'bookings_pending_today' => Booking::whereDate('booking_date', today())
                ->whereIn('status', ['pending', 'approved_dosen', 'approved_teknisi'])
                ->count(),
        ];

        // ✅ Jika request AJAX untuk stats only (real-time update)
        if ($request->ajax() && $request->has('stats_only')) {
            return response()->json([
                'bookings_today' => $stats['bookings_today'],
                'bookings_this_month' => $stats['bookings_this_month'],
                'bookings_confirmed_today' => $stats['bookings_confirmed_today'],
                'bookings_pending_today' => $stats['bookings_pending_today'],
                'timestamp' => now()->format('H:i:s'),
            ]);
        }

        // ====================================================================
        // ✅ ANALYTICS DATA FOR CHARTS
        // ====================================================================

        // 📊 1. Lab Paling Sering Dipinjam (Last 30 days, confirmed only)
        $labAnalytics = Booking::select('lab_name', DB::raw('count(*) as total'))
            ->whereDate('booking_date', '>=', now()->subDays(30))
            ->where('status', 'confirmed')
            ->groupBy('lab_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $chartLabLabels = $labAnalytics->pluck('lab_name')->toArray();
        $chartLabData = $labAnalytics->pluck('total')->toArray();

        // 📊 2. Hari Paling Banyak Dipilih (All confirmed bookings)
        $dayAnalytics = Booking::selectRaw('DAYNAME(booking_date) as day_name, COUNT(*) as total')
            ->where('status', 'confirmed')
            ->groupBy('day_name')
            ->orderByRaw('FIELD(day_name, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        // Map hari Inggris ke Indonesia
        $dayMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $chartDayLabels = $dayAnalytics->map(fn($d) => $dayMap[$d->day_name] ?? $d->day_name)->toArray();
        $chartDayData = $dayAnalytics->pluck('total')->toArray();

        // 📊 3. Jenis Kegiatan Peminjaman (Activity distribution)
        $activityAnalytics = Booking::select('activity', DB::raw('count(*) as total'))
            ->where('status', 'confirmed')
            ->whereDate('booking_date', '>=', now()->subDays(90)) // Last 3 months
            ->groupBy('activity')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $chartActivityLabels = $activityAnalytics->pluck('activity')->toArray();
        $chartActivityData = $activityAnalytics->pluck('total')->toArray();

        // 📊 4. Top Peminjam (Users with most confirmed bookings)
        $topBorrowers = Booking::select('user_id', DB::raw('count(*) as total'))
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

        // ====================================================================
        // ✅ SCHEDULE DATA: Auto-load untuk hari ini (tanpa search)
        // ====================================================================
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');

        $currentTime = Carbon::now('Asia/Jakarta');
        $today = $currentTime->toDateString();
        $todayName = $currentTime->isoFormat('dddd');

        // ✅ DEFAULT: Gunakan tanggal hari ini (auto-load, tidak perlu user pilih)
        $scheduleDate = $request->get('date', $today);
        $scheduleDayName = Carbon::parse($scheduleDate)->isoFormat('dddd');

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
                    $scheduleDate, $scheduleDayName, $today, $todayName
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
                ];
            }
            $scheduleData[$lab] = $sessionsData;
        }

        // ✅ FIX: Buat variabel realtimeDayName agar bisa dipakai di compact()
        $realtimeDayName = $todayName;

        // ✅ Return view dengan semua data (gunakan array_merge untuk variabel custom)
        return view('admin.dashboard', array_merge(compact(
            'stats',
            'currentTime',
            'realtimeDayName',
            'scheduleDate',
            'scheduleDayName',
            'labs',
            'scheduleData',
            // ✅ Analytics for charts
            'chartLabLabels', 'chartLabData',
            'chartDayLabels', 'chartDayData',
            'chartActivityLabels', 'chartActivityData',
            'chartBorrowerLabels', 'chartBorrowerData', 'chartBorrowerRoles'
        )));
    }

    /**
     * ✅ HELPER: Get session status untuk jadwal lab
     */
    private function getSessionStatusFromDb($labName, $startTime, $endTime, $scheduleDate, $scheduleDayName, $today, $todayName)
    {
        $currentTime = Carbon::now('Asia/Jakarta');
        $now = $currentTime->format('H:i');

        $scheduleDateObj = Carbon::parse($scheduleDate)->startOfDay();
        $todayObj = Carbon::parse($today)->startOfDay();

        $isToday = $scheduleDateObj->equalTo($todayObj);
        $isFutureDate = $scheduleDateObj->greaterThan($todayObj);
        $isPastDate = $scheduleDateObj->lessThan($todayObj);

        // 🔹 Check Class Schedule (Jadwal Kuliah)
        $dayMap = [
            'Senin' => ['Senin', 'Monday'], 'Selasa' => ['Selasa', 'Tuesday'],
            'Rabu' => ['Rabu', 'Wednesday'], 'Kamis' => ['Kamis', 'Thursday'],
            'Jumat' => ['Jumat', 'Friday'], 'Sabtu' => ['Sabtu', 'Saturday'],
            'Minggu' => ['Minggu', 'Sunday'],
        ];
        $possibleDays = $dayMap[$scheduleDayName] ?? [$scheduleDayName];

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
                    ];
                }
                if ($isToday && $now >= $startTime && $now < $endTime) {
                    return [
                        'status' => 'proses',
                        'label' => '🎓 Kuliah Berlangsung',
                        'color' => 'yellow',
                        'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan,
                    ];
                }
                return [
                    'status' => 'terisi',
                    'label' => '📚 Terisi - Jadwal Kuliah',
                    'color' => 'red',
                    'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan,
                ];
            }
        }

        // 🔹 Check Bookings (Peminjaman)
        $bookings = Booking::where('lab_name', $labName)
            ->whereDate('booking_date', $scheduleDate)
            ->whereIn('status', ['confirmed', 'pending', 'approved_dosen', 'approved_teknisi'])
            ->with('user')
            ->get();

        foreach ($bookings as $booking) {
            $bStart = substr($booking->start_time ?? '', 0, 5);
            $bEnd = substr($booking->end_time ?? '', 0, 5);
            if (empty($bStart) || empty($bEnd)) continue;
            if ($startTime < $bEnd && $endTime > $bStart) {
                $statusMap = match($booking->status) {
                    'confirmed' => [
                        'status' => 'terisi',
                        'label' => '🔒 Terisi - Booking',
                        'color' => 'red',
                        'info' => ($booking->user->name ?? 'Unknown') . ' - ' . substr($booking->purpose ?? '', 0, 30),
                    ],
                    'pending' => [
                        'status' => 'pending',
                        'label' => '⏳ Menunggu Dosen',
                        'color' => 'orange',
                        'info' => ($booking->user->name ?? 'Unknown'),
                    ],
                    'approved_dosen' => [
                        'status' => 'approved_dosen',
                        'label' => '✅ Disetujui Dosen',
                        'color' => 'blue',
                        'info' => ($booking->user->name ?? 'Unknown'),
                    ],
                    'approved_teknisi' => [
                        'status' => 'approved_teknisi',
                        'label' => '✅ Disetujui Teknisi',
                        'color' => 'indigo',
                        'info' => ($booking->user->name ?? 'Unknown'),
                    ],
                    default => [
                        'status' => 'unknown',
                        'label' => '❓ Unknown',
                        'color' => 'gray',
                        'info' => '',
                    ],
                };
                // Jika hari ini dan sedang berlangsung
                if ($isToday && $now >= $startTime && $now < $endTime && $booking->status === 'confirmed') {
                    $statusMap['label'] = '🔄 Sedang Berlangsung';
                    $statusMap['color'] = 'yellow';
                    $statusMap['status'] = 'proses';
                }
                return $statusMap + ['booking_id' => $booking->id];
            }
        }

        // 🔹 Default: Tersedia / Selesai
        if ($isFutureDate) {
            return ['status' => 'tersedia', 'label' => '✅ Tersedia', 'color' => 'green'];
        }
        if ($isPastDate) {
            return ['status' => 'selesai', 'label' => '⏹️ Selesai', 'color' => 'gray'];
        }
        if ($now >= $startTime && $now < $endTime) {
            return ['status' => 'tersedia', 'label' => '✅ Tersedia', 'color' => 'green'];
        }
        if ($now < $startTime) {
            return ['status' => 'tersedia', 'label' => '✅ Tersedia', 'color' => 'green'];
        }
        return ['status' => 'selesai', 'label' => '⏹️ Selesai', 'color' => 'gray'];
    }

    // ========================================================================
    // 👥 USER MANAGEMENT METHODS
    // ========================================================================

    /**
     * Display list of users
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show form to create new user
     */
    public function createUser()
    {
        $roles = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'ketua_lab' => 'Ketua Lab',
            'teknisi' => 'Teknisi',
            'admin' => 'Admin',
        ];

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:mahasiswa,dosen,ketua_lab,teknisi,admin'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Validasi domain email berdasarkan role
        $domain = substr(strrchr($validated['email'], "@"), 1);

        if ($request->role === 'mahasiswa') {
            if ($domain !== 'student.polije.ac.id') {
                return back()->withErrors(['email' => 'Email mahasiswa harus menggunakan domain @student.polije.ac.id'])->withInput();
            }
            // Auto-generate NIM jika belum ada (opsional)
            if (empty($request->nim)) {
                $validated['nim'] = 'MHS' . now()->format('Ymd') . rand(1000, 9999);
            }
        } else {
            if ($domain !== 'polije.ac.id') {
                return back()->withErrors(['email' => 'Email staff harus menggunakan domain @polije.ac.id'])->withInput();
            }
            // Auto-generate NIP untuk dosen/staff jika belum ada (opsional)
            if (empty($request->nip) && in_array($request->role, ['dosen', 'ketua_lab', 'teknisi'])) {
                $validated['nip'] = 'STF' . now()->format('Ymd') . rand(1000, 9999);
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'nim' => $validated['nim'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users')->with('success', '✅ User berhasil ditambahkan!');
    }

    /**
     * Show user detail
     */
    public function showUser(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show form to edit user
     */
    public function editUser(User $user)
    {
        $roles = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'ketua_lab' => 'Ketua Lab',
            'teknisi' => 'Teknisi',
            'admin' => 'Admin',
        ];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:mahasiswa,dosen,ketua_lab,teknisi,admin'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ];

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', '✅ User berhasil diupdate!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => '❌ Anda tidak bisa menghapus akun sendiri!']);
        }

        // Prevent deleting last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['error' => '❌ Tidak dapat menghapus admin terakhir!']);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', '✅ User berhasil dihapus!');
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        $newPassword = Str::random(10);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return back()->with('success', "✅ Password berhasil direset! Password baru: <strong>{$newPassword}</strong>");
    }

    /**
     * Toggle Kalab status for user
     */
    public function toggleKalabStatus(User $user)
    {
        if (!in_array($user->role, ['dosen', 'ketua_lab'])) {
            return back()->withErrors(['error' => 'Hanya dosen/ketua lab yang dapat dijadikan Kalab']);
        }

        $user->update([
            'is_kalab' => !$user->is_kalab,
        ]);

        return back()->with('success', $user->is_kalab
            ? '✅ User kini memiliki akses Kalab'
            : '✅ Akses Kalab user telah dicabut');
    }
}
