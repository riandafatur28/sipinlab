<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Lab;
use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Main dashboard router based on user role
     */
    public function index()
    {
        $user = Auth::user();

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

        $selectedDay = request('day');
        return $this->getDashboardData('dashboard.mahasiswa', $selectedDay);
    }

    /**
     * Dashboard for Staff (Dosen, Teknisi, Ka Lab, Admin)
     */
    public function staff()
    {
        $allowedRoles = ['dosen', 'ketua_lab', 'teknisi', 'staff', 'admin'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            abort(403, 'Unauthorized');
        }

        $selectedDay = request('day');
        return $this->getDashboardData('dashboard.staff', $selectedDay);
    }

    /**
     * Get dashboard data from database
     * ✅ FIXED: Schedule updates when booking is created/approved
     */
    private function getDashboardData($viewName, $selectedDay = null)
    {
        // ✅ Set timezone & locale
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');
        
        $currentTime = Carbon::now('Asia/Jakarta');
        $today = $currentTime->toDateString();
        
        // ✅ VARIABEL 1: Untuk realtime clock (SELALU hari ini)
        $realtimeDayName = $currentTime->isoFormat('dddd');
        
        // ✅ VARIABEL 2: Untuk filter jadwal (bisa dipilih user)
        $scheduleDayName = $selectedDay ?? $realtimeDayName;

        // ✅ Get today's name for status logic
        $todayName = $currentTime->isoFormat('dddd');

        // ✅ LABS dari database
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
                // ✅ Gunakan $scheduleDayName untuk query jadwal
                $status = $this->getSessionStatusFromDb(
                    $lab, 
                    $session['start'], 
                    $session['end'], 
                    $scheduleDayName,
                    $today,
                    $todayName
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
                ];
            }
            $scheduleData[$lab] = $sessionsData;
        }

        // ✅ Kirim data ke view
        return view($viewName, compact(
            'scheduleData', 
            'currentTime', 
            'labs', 
            'realtimeDayName',
            'scheduleDayName'
        ));
    }

    /**
     * Get session status from database
     * ✅ FIXED: Check bookings for selected day + all relevant statuses
     */
    private function getSessionStatusFromDb($labName, $startTime, $endTime, $currentDay, $today, $todayName = null)
    {
        $currentTime = Carbon::now('Asia/Jakarta');
        $now = $currentTime->format('H:i');

        // Mapping hari Indonesia <-> Inggris
        $dayMap = [
            'Senin' => ['Senin', 'Monday'],
            'Selasa' => ['Selasa', 'Tuesday'],
            'Rabu' => ['Rabu', 'Wednesday'],
            'Kamis' => ['Kamis', 'Thursday'],
            'Jumat' => ['Jumat', 'Friday'],
            'Sabtu' => ['Sabtu', 'Saturday'],
            'Minggu' => ['Minggu', 'Sunday'],
        ];
        
        $possibleDays = $dayMap[$currentDay] ?? [$currentDay];

        // ✅ Get class schedules for this lab and selected day
        $classSchedules = ClassSchedule::where('lab_name', $labName)
            ->whereIn('day', $possibleDays)
            ->where('status', 'active')
            ->get();

        // ✅ Check class schedule overlap
        foreach ($classSchedules as $schedule) {
            $dbStart = substr($schedule->start_time, 0, 5);
            $dbEnd = substr($schedule->end_time, 0, 5);
            
            if ($startTime < $dbEnd && $endTime > $dbStart) {
                // ✅ "Kuliah Berlangsung" hanya untuk hari ini + waktu sekarang
                if ($currentDay === $todayName && $now >= $startTime && $now < $endTime) {
                    return [
                        'status' => 'proses',
                        'label' => 'Kuliah Berlangsung',
                        'color' => 'yellow',
                        'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan . ' (' . ($schedule->lecturer->name ?? 'Unknown') . ')',
                    ];
                } else {
                    return [
                        'status' => 'terisi',
                        'label' => 'Terisi - Jadwal Kuliah',
                        'color' => 'red',
                        'info' => $schedule->course_name . ' - Gol. ' . $schedule->golongan,
                    ];
                }
            }
        }

        // ✅ FIX UTAMA: Check bookings untuk HARI YANG DIPILIH, bukan hanya hari ini
        // ✅ Konversi nama hari ke tanggal untuk query
        $targetDate = $this->getDateFromDayName($currentDay, $today);
        
        // ✅ Query bookings: hanya 'confirmed' yang memblokir slot di schedule
        $bookings = Booking::where('lab_name', $labName)
            ->whereDate('booking_date', $targetDate)
            ->where('status', 'confirmed')  // ✅ Hanya confirmed yang blocking
            ->with('user')
            ->get();

        foreach ($bookings as $booking) {
            $bStart = substr($booking->start_time ?? '', 0, 5);
            $bEnd = substr($booking->end_time ?? '', 0, 5);
            
            if (empty($bStart) || empty($bEnd)) {
                continue;
            }
            
            if ($startTime < $bEnd && $endTime > $bStart) {
                // ✅ "Proses Peminjaman" hanya untuk hari ini + waktu sekarang
                if ($currentDay === $todayName && $now >= $startTime && $now < $endTime) {
                    return [
                        'status' => 'proses',
                        'label' => 'Proses Peminjaman',
                        'color' => 'yellow',
                        'info' => $booking->user->name ?? 'Unknown',
                    ];
                }
                return [
                    'status' => 'terisi',
                    'label' => 'Terisi - Booking',
                    'color' => 'red',
                    'info' => $booking->user->name ?? 'Unknown',
                ];
            }
        }

        // ✅ Default: Tersedia atau Selesai
        if ($now >= $startTime && $now < $endTime) {
            return [
                'status' => 'tersedia',
                'label' => 'Tersedia',
                'color' => 'green',
                'info' => null,
            ];
        } elseif ($now < $startTime) {
            return [
                'status' => 'tersedia',
                'label' => 'Tersedia',
                'color' => 'green',
                'info' => null,
            ];
        } else {
            return [
                'status' => 'selesai',
                'label' => 'Selesai',
                'color' => 'gray',
                'info' => null,
            ];
        }
    }

    /**
     * ✅ HELPER: Convert day name to date in current week
     * Example: "Selasa" + "2026-02-24" → "2026-02-24" (if today is Selasa)
     */
    private function getDateFromDayName($dayName, $today)
    {
        // Mapping hari Indonesia ke angka (1 = Senin, 7 = Minggu)
        $dayToNum = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4,
            'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7,
            'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
            'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
        ];
        
        $targetDayNum = $dayToNum[$dayName] ?? null;
        if (!$targetDayNum) {
            return $today; // Fallback ke today jika hari tidak dikenali
        }
        
        $todayObj = Carbon::parse($today);
        $todayDayNum = $todayObj->dayOfWeekIso; // 1 = Senin, 7 = Minggu
        
        // Hitung selisih hari
        $diff = $targetDayNum - $todayDayNum;
        
        // Return tanggal yang sesuai
        return $todayObj->copy()->addDays($diff)->toDateString();
    }

    /**
     * Booking endpoint (API)
     */
    public function booking(Request $request)
    {
        $validated = $request->validate([
            'lab_name' => 'required|string',
            'session' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'booking_date' => 'required|date|after_or_equal:today',
            'purpose' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        
        $status = match($user->role) {
            'mahasiswa' => 'pending',
            'dosen' => 'pending',
            'teknisi' => 'approved_teknisi',
            'ketua_lab' => 'confirmed',
            'admin' => 'confirmed',
            default => 'pending',
        };

        // ✅ Hitung start_date dan end_date dari booking_date + duration
        $bookingDate = Carbon::parse($validated['booking_date']);
        $startDate = $bookingDate->copy();
        $endDate = $bookingDate->copy(); // Default 1 hari

        $booking = Booking::create([
            'user_id' => $user->id,
            'lab_name' => $validated['lab_name'],
            'session' => $validated['session'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'booking_date' => $validated['booking_date'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration_days' => 1,
            'purpose' => $validated['purpose'],
            'phone' => $user->phone ?? '',
            'prodi' => $user->prodi ?? 'Teknik Informatika',
            'golongan' => $user->golongan ?? '-',
            'is_group' => false,
            'status' => $status,
        ]);

        // ✅ Log untuk debug
        \Log::info('Booking created via API', [
            'booking_id' => $booking->id,
            'lab' => $validated['lab_name'],
            'date' => $validated['booking_date'],
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil diajukan!',
            'booking_id' => $booking->id,
            'redirect' => route('booking.index') . '?refresh=1', // ✅ Trigger refresh
        ]);
    }
}