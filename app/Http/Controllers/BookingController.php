<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Lab;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    /**
     * Display booking dashboard (Unified View for Admin, Teknisi, Kalab)
     * ⚠️ UPDATED: Mahasiswa & Dosen akan diarahkan langsung ke Form Peminjaman
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // ✅ LOGIKA BARU: Alihkan Mahasiswa & Dosen ke Halaman Buat Booking (Form)
        if ($user->role === 'mahasiswa' || $user->role === 'dosen') {
            return redirect()->route('booking.create');
        }

        // --- DATA & FILTER SETUP (Hanya untuk Staff/Admin/Kalab/Teknisi) ---

        // Ambil semua Lab untuk Dropdown Filter
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

        // Prepare Base Query
        $query = Booking::with(['user'])->orderBy('created_at', 'desc');

        // 1. 🚫 ROLE FILTER (Teknisi hanya lihat lab sendiri)
        if ($user->isTeknisi() && !empty($user->lab_name)) {
            $query->where('lab_name', $user->lab_name);
        } else {
            // Untuk Admin/Kalab/Dosen jika perlu filter default
            if ($request->filled('default_lab')) {
                $query->where('lab_name', $request->default_lab);
            }
        }

        // 2. 🔍 SEARCH FILTER (Name/NIM/NIP)
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nim', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // 3. 🏢 LAB FILTER
        if ($request->filled('lab')) {
            $query->where('lab_name', $request->lab);
        }

        // 4. 📅 DATE FILTER
        if ($request->filled('date_start') || $request->filled('date_end')) {
            $startDate = $request->filled('date_start') ? Carbon::parse($request->date_start)->startOfDay()->toDateString() : null;
            $endDate = $request->filled('date_end') ? Carbon::parse($request->date_end)->endOfDay()->toDateString() : null;

            if ($startDate && $endDate) {
                $query->whereBetween('booking_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->whereDate('booking_date', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('booking_date', '<=', $endDate);
            }
        }

        // 5. ⚠️ STATUS FILTER
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Get Statistics (Global atau Filtered - Sesuaikan kebutuhan)
        // Di sini kita hitung berdasarkan query yang sudah difilter agar sesuai tampilan
        $filteredStatsQuery = clone $query;

        $stats = [
            'total_booking' => $filteredStatsQuery->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'approved_dosen'])->count(),
            'hari_ini' => (clone $query)->whereDate('booking_date', today())->count(),
        ];

        // Paginate results
        $bookings = $query->paginate(10)->withQueryString();

        // FIX: Siapkan data terpisah agar bisa digabungkan dengan array kustom di compact
        $request_filter_values = $request->all();

        return view('booking.index', compact(
            'bookings',
            'stats',
            'user',
            'labs',
            'request_filter_values'
        ));
    }

    /**
     * ⚠️ NEW: Hapus booking - Untuk Admin, Kalab, Teknisi
     */
    public function destroy(Request $request, Booking $booking)
    {
        $user = Auth::user();

        // Authorization check
        if (!$user->isAdmin() && !$user->isKalab() && $user->role !== 'ketua_lab') {
            abort(403, 'Anda tidak berwenang menghapus booking ini');
        }

        // Teknisi hanya bisa hapus booking di lab mereka
        if ($user->role === 'teknisi' && $user->lab_name !== $booking->lab_name) {
            abort(403, 'Anda tidak dapat menghapus booking untuk laboratorium ini');
        }

        // Cek apakah booking sudah confirmed (bisa dihapus atau perlu warning?)
        if ($booking->isConfirmed()) {
            return back()->withErrors(['error' => '⚠️ Booking sudah dikonfirmasi. Pembatalan akan dicatat di log sistem.']);
        }

        // Log sebelum hapus
        $deletedByRole = $user->role === 'admin' ? 'Admin' :
                        ($user->isKalab() || $user->role === 'ketua_lab' ? 'Ka Lab' : 'Teknisi');

        Log::info('Booking deleted', [
            'booking_id' => $booking->id,
            'deleted_by_role' => $deletedByRole,
            'reason' => 'Dihapus oleh staff',
        ]);

        $booking->delete();

        return redirect()->back()
            ->with('success', '✅ Booking berhasil dihapus!');
    }

    /**
     * ⚠️ NEW: Download booking form (PDF) - Setelah di-acc Kalab
     */
    public function downloadFormAfterApproved(Booking $booking)
    {
        $user = Auth::user();

        // Permission: Hanya yang bisa approve dan booking itu sudah confirmed
        if (!$user->isAdmin() && !$user->isKalab() && $user->role !== 'ketua_lab') {
            abort(403, 'Anda tidak berwenang mengunduh formulir');
        }

        if (!$booking->isConfirmed()) {
            return back()->withErrors(['error' => 'Booking belum dikonfirmasi, formulir belum tersedia']);
        }

        $approvalDate = $booking->approved_at_kalab ?? now();

        // Generate PDF
        // Pastikan file view ada di resources/views/booking/form-approved.blade.php
        $pdf = Pdf::loadView('booking.form-approved', compact('booking', 'approvalDate'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Form-Booking-' . str_replace(' ', '-', $booking->lab_name) . '-' . $booking->id . '-' . $booking->booking_date->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * OLD: Print booking form (A4 size) - Only for admin, teknisi, and kalab
     */
    public function printForm(Booking $booking)
    {
        $user = Auth::user();

        // Update authorization untuk support Kalab
        if (!in_array($user->role, ['admin', 'teknisi', 'ketua_lab']) && !$user->isKalab()) {
            abort(403, 'Anda tidak berwenang mencetak formulir ini');
        }

        $approvalDate = $booking->approved_at_kalab ?? now();

        return view('booking.print-form', compact('booking', 'approvalDate'));
    }

    /**
     * OLD: Download booking form as PDF
     */
    public function downloadPDF(Booking $booking)
    {
        $user = Auth::user();

        // Update authorization untuk support Kalab
        if (!in_array($user->role, ['admin', 'teknisi', 'ketua_lab']) && !$user->isKalab()) {
            abort(403, 'Anda tidak berwenang mengunduh formulir ini');
        }

        $approvalDate = $booking->approved_at_kalab ?? now();

        $pdf = Pdf::loadView('booking.print-form', compact('booking', 'approvalDate'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Form-Peminjaman-' . str_replace(' ', '-', $booking->lab_name) . '-' . $booking->id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Approve booking by Ka Lab (Final approval)
     */
    public function approveByKalab(Request $request, Booking $booking)
    {
        $user = Auth::user();

        // ✅ UPDATED: Support isKalab() method
        if (!$user->isKalab() && $user->role !== 'ketua_lab' && !$user->isAdmin()) {
            abort(403, 'Hanya Ketua Lab atau Admin yang dapat melakukan konfirmasi final');
        }

        if (!$booking->canApproveByKalab()) {
            return back()->withErrors(['error' => 'Booking harus disetujui teknisi terlebih dahulu']);
        }

        $booking->update([
            'status' => 'confirmed',
            'approved_by_kalab' => $user->id,
            'approved_at_kalab' => now(),
        ]);

        Log::info('Booking confirmed by kalab', [
            'booking_id' => $booking->id,
            'kalab_id' => $user->id,
            'kalab_name' => $user->name,
        ]);

        return redirect()->route('booking.show', $booking)
            ->with('success', '🎉 Booking BERHASIL DIKONFIRMASI! Silakan gunakan lab sesuai jadwal.');
    }

    /**
     * Show booking form for creating new booking (General - Mahasiswa)
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        $prefilled = [
            'lab_name' => $request->get('lab'),
            'session' => $request->get('session'),
            'start_time' => $request->get('start_time'),
            'end_time' => $request->get('end_time'),
            'booking_date' => $request->get('date'),
        ];

        $dosens = User::where('role', 'dosen')->orderBy('name')->get(['id', 'name', 'email']);
        $students = User::where('role', 'mahasiswa')->orderBy('name')->get(['id', 'name', 'email', 'nim']);
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get(['id', 'name', 'email']);

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

        $activities = [
            'mahasiswa' => [
                'Tugas Kuliah',
                'Tugas Akhir',
                'Praktikum',
                'Penelitian',
                'Lomba/Kompetisi',
                'Kegiatan Komunitas',
                'Lainnya',
            ],
            'dosen' => [
                'Bimbingan - Tugas Akhir Workshop',
                'Penelitian',
                'Pengabdian Masyarakat',
                'Perkuliahan (Workshop) Prodi Lain',
                'Bimbingan Lainnya',
                'Lainnya',
            ],
        ];

        return view('booking.create', compact(
            'user',
            'dosens',
            'students',
            'lecturers',
            'labs',
            'activities',
            'prefilled'
        ));
    }

    /**
     * Show booking form khusus untuk Dosen
     */
    public function createDosen(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'dosen') {
            abort(403, 'Halaman ini hanya untuk dosen');
        }

        $prefilled = [
            'lab_name' => $request->get('lab'),
            'session' => $request->get('session'),
            'start_time' => $request->get('start_time'),
            'end_time' => $request->get('end_time'),
            'booking_date' => $request->get('date'),
        ];

        $students = User::where('role', 'mahasiswa')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'email', 'nim']);

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

        $activities = [
            'Bimbingan Tugas Akhir / Skripsi',
            'Praktikum Mata Kuliah',
            'Penelitian / Riset',
            'Pengabdian Masyarakat',
            'Workshop / Seminar',
            'Ujian / Evaluasi',
            'Rapat Koordinasi Prodi',
            'Lainnya',
        ];

        return view('booking.dosen-create', compact(
            'user',
            'students',
            'labs',
            'activities',
            'prefilled'
        ));
    }

    /**
     * Store new booking in database
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'dosen') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'nip' => 'required|string|size:18',
                'phone' => 'required|string|max:20',
                'lab_name' => 'required|string|max:255',
                'session' => 'required|string|max:100',
                'booking_date' => 'required|date|after_or_equal:today',
                'duration_days' => 'required|integer|min:1|max:30',
                'activity' => 'required|string|max:255',
                'activity_other' => 'nullable|string|max:255|required_if:activity,Lainnya',
                'purpose' => 'required|string|max:1000',
                'with_students' => 'nullable|boolean',
                'students' => 'nullable|array',
                'students.*' => 'exists:users,id',
                'use_custom_time' => 'nullable|boolean',
                'start_time_custom' => 'nullable|date_format:H:i',
                'end_time_custom' => 'nullable|date_format:H:i|after:start_time_custom',
                'equipment_needs' => 'nullable|string|max:500',
                'agreement' => 'accepted|required',
            ], [
                'agreement.accepted' => 'Anda harus menyetujui pernyataan tanggung jawab',
                'nip.required' => 'NIP wajib diisi',
                'nip.size' => 'NIP harus terdiri dari 18 digit angka',
                'name.required' => 'Nama lengkap wajib diisi',
                'phone.required' => 'No. Telepon wajib diisi',
            ]);

            if ($user->name !== $validated['name'] ||
                $user->nip !== $validated['nip'] ||
                $user->phone !== $validated['phone']) {

                $user->update([
                    'name' => $validated['name'],
                    'nip' => $validated['nip'],
                    'phone' => $validated['phone'],
                ]);
            }

            $status = 'pending';

        } else {
            $validated = $request->validate([
                'lab_name' => 'required|string|max:255',
                'session' => 'required|string|max:100',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'booking_date' => 'required|date|after_or_equal:today',
                'duration_days' => 'required|integer|min:1|max:30',
                'activity' => 'required|string|max:255',
                'activity_other' => 'nullable|string|max:255|required_if:activity,Lainnya',
                'purpose' => 'required|string|max:1000',
                'phone' => 'required|string|max:20',
                'prodi' => 'required|string|max:255',
                'golongan' => 'required|string|max:10',
                'is_group' => 'nullable|boolean',
                'members' => 'nullable|array',
                'members.*' => 'exists:users,id',
                'supervisor_id' => 'nullable|exists:users,id',
                'agreement' => 'accepted|required',
            ]);

            $status = match($user->role) {
                'mahasiswa' => 'pending',
                'teknisi' => 'approved_teknisi',
                'ketua_lab' => 'confirmed',
                'admin' => 'confirmed',
                default => 'pending',
            };
        }

        $bookingDate = Carbon::parse($validated['booking_date']);
        $startDate = $bookingDate->copy();
        $endDate = $bookingDate->copy()->addDays($validated['duration_days'] - 1);

        // Check time slot conflict (from previous implementation)
        $startTime = $validated['start_time_custom'] ?? ($validated['start_time'] ?? '07:00');
        $endTime = $validated['end_time_custom'] ?? ($validated['end_time'] ?? '08:00');

        $conflictCheck = $this->checkTimeSlotConflict(
            $validated['lab_name'],
            $validated['booking_date'],
            $startTime,
            $endTime
        );

        if (!$conflictCheck['available']) {
            Log::warning('Booking rejected due to conflict', [
                'user_id' => $user->id,
                'lab' => $validated['lab_name'],
                'date' => $validated['booking_date'],
                'time' => "{$startTime} - {$endTime}",
                'conflict_type' => $conflictCheck['conflict_type'],
                'conflict_info' => $conflictCheck['conflict_info'],
            ]);

            return back()->withInput()->withErrors([
                'booking_date' => "❌ Waktu yang diajukan tidak tersedia. {$conflictCheck['conflict_info']}. Silakan pilih waktu lain.",
            ]);
        }

        $booking = Booking::create([
            'user_id' => $user->id,
            'lab_name' => $validated['lab_name'],
            'session' => $validated['session'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'booking_date' => $validated['booking_date'],
            'activity' => $validated['activity'] === 'Lainnya'
                ? ($validated['activity_other'] ?? 'Lainnya')
                : $validated['activity'],
            'purpose' => $validated['purpose'],
            'phone' => $validated['phone'],
            'prodi' => $validated['prodi'] ?? 'Teknik Informatika',
            'golongan' => $validated['golongan'] ?? ($user->golongan ?? '-'),
            'is_group' => $validated['with_students'] ?? ($validated['is_group'] ?? false),
            'members' => $validated['students'] ?? ($validated['members'] ?? []),
            'supervisor_id' => null,
            'duration_days' => $validated['duration_days'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'status' => $status,
            'notes' => $validated['equipment_needs'] ?? ($request->input('notes') ?? null),
        ]);

        Log::info('Booking created successfully', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'lab' => $validated['lab_name'],
            'status' => $status,
        ]);

        $message = $user->role === 'dosen'
            ? '✅ Peminjaman berhasil diajukan! Menunggu persetujuan teknisi.'
            : '✅ Booking berhasil diajukan! ' . $this->getStatusMessage($status);

        return redirect()->route('booking.index')
            ->with('success', $message);
    }

    /**
     * Search users by NIM or name for member selection
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('query', '');
        $type = $request->get('type', 'student');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('nim', 'like', "%{$query}%")
              ->orWhere('nip', 'like', "%{$query}%");
        });

        if ($type === 'student') {
            $users->where('role', 'mahasiswa');
        } elseif ($type === 'lecturer') {
            $users->where('role', 'dosen');
        }

        $results = $users->limit(10)->get(['id', 'name', 'email', 'role', 'nim', 'nip']);

        return response()->json($results);
    }

    /**
     * Show booking detail
     */
    public function show(Booking $booking)
    {
        $user = Auth::user();

        if ($booking->user_id !== $user->id && !in_array($user->role, ['dosen', 'teknisi', 'ketua_lab', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        return view('booking.show', compact('booking'));
    }

    /**
     * Approve booking by Dosen (for student bookings)
     */
    public function approveByDosen(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->role !== 'dosen') {
            abort(403, 'Hanya dosen yang dapat menyetujui booking mahasiswa');
        }

        if (!$booking->canApproveByDosen()) {
            return back()->withErrors(['error' => 'Booking ini tidak dapat disetujui oleh dosen']);
        }

        $booking->update([
            'status' => 'approved_dosen',
            'approved_by_dosen' => $user->id,
            'approved_at_dosen' => now(),
        ]);

        Log::info('Booking approved by dosen', [
            'booking_id' => $booking->id,
            'dosen_id' => $user->id,
        ]);

        return back()->with('success', '✅ Booking berhasil disetujui! Menunggu persetujuan teknisi.');
    }

    /**
     * Approve booking by Teknisi - Validate lab assignment
     */
    public function approveByTeknisi(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->role !== 'teknisi' || $user->lab_name !== $booking->lab_name) {
            abort(403, 'Anda tidak berwenang menyetujui booking untuk laboratorium ini');
        }

        if (!$booking->canApproveByCurrentTeknisi()) {
            return back()->withErrors(['error' => 'Booking ini belum dapat disetujui oleh teknisi']);
        }

        $booking->update([
            'status' => 'approved_teknisi',
            'approved_by_teknisi' => $user->id,
            'approved_at_teknisi' => now(),
        ]);

        Log::info('Booking approved by teknisi', [
            'booking_id' => $booking->id,
            'teknisi_id' => $user->id,
            'lab' => $booking->lab_name,
        ]);

        return back()->with('success', '✅ Booking berhasil disetujui! Menunggu persetujuan Ka Lab.');
    }

    /**
     * Reject booking
     */
    public function reject(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $allowedRoles = ['dosen', 'teknisi', 'ketua_lab', 'admin'];

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized');
        }

        if ($user->role === 'teknisi' && $user->lab_name !== $booking->lab_name) {
            abort(403, 'Anda tidak berwenang menolak booking untuk laboratorium ini');
        }

        if (in_array($booking->status, ['confirmed', 'rejected'])) {
            return back()->withErrors(['error' => 'Booking ini sudah tidak dapat ditolak']);
        }

        $booking->update([
            'status' => 'rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        Log::info('Booking rejected', [
            'booking_id' => $booking->id,
            'rejected_by' => $user->id,
            'reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', '❌ Booking ditolak. Alasan: ' . $validated['rejection_reason']);
    }

    /**
     * Cancel booking (by owner only)
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($booking->user_id !== $user->id) {
            abort(403, 'Hanya pembuat booking yang dapat membatalkan');
        }

        if ($booking->isConfirmed()) {
            return back()->withErrors(['error' => 'Booking yang sudah dikonfirmasi tidak dapat dibatalkan. Hubungi admin.']);
        }

        $booking->update([
            'status' => 'cancelled',
            'rejected_at' => now(),
            'rejection_reason' => 'Dibatalkan oleh pemohon',
        ]);

        return back()->with('success', '🗑️ Booking berhasil dibatalkan');
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function checkTimeSlotConflict(string $labName, string $bookingDate, string $startTime, string $endTime): array
    {
        $startTime = substr($startTime, 0, 5);
        $endTime = substr($endTime, 0, 5);

        // Check Class Schedule
        $dayName = Carbon::parse($bookingDate)->isoFormat('dddd');
        $dayMap = [
            'Senin' => ['Senin', 'Monday'],
            'Selasa' => ['Selasa', 'Tuesday'],
            'Rabu' => ['Rabu', 'Wednesday'],
            'Kamis' => ['Kamis', 'Thursday'],
            'Jumat' => ['Jumat', 'Friday'],
            'Sabtu' => ['Sabtu', 'Saturday'],
            'Minggu' => ['Minggu', 'Sunday'],
        ];
        $possibleDays = $dayMap[$dayName] ?? [$dayName];

        $classSchedules = ClassSchedule::where('lab_name', $labName)
            ->whereIn('day', $possibleDays)
            ->where('status', 'active')
            ->get();

        foreach ($classSchedules as $schedule) {
            $csStart = substr($schedule->start_time, 0, 5);
            $csEnd = substr($schedule->end_time, 0, 5);

            if ($startTime < $csEnd && $endTime > $csStart) {
                return [
                    'available' => false,
                    'conflict_type' => 'class_schedule',
                    'conflict_info' => "Bentrok dengan jadwal kuliah: {$schedule->course_name} (Gol. {$schedule->golongan})",
                ];
            }
        }

        // Check Other Bookings
        $blockingStatuses = ['confirmed', 'pending', 'approved_dosen', 'approved_teknisi'];

        $conflictingBookings = Booking::where('lab_name', $labName)
            ->whereDate('booking_date', $bookingDate)
            ->whereIn('status', $blockingStatuses)
            ->with('user')
            ->get();

        foreach ($conflictingBookings as $booking) {
            $bStart = substr($booking->start_time ?? '', 0, 5);
            $bEnd = substr($booking->end_time ?? '', 0, 5);

            if (empty($bStart) || empty($bEnd)) continue;

            if ($startTime < $bEnd && $endTime > $bStart) {
                $statusLabel = match($booking->status) {
                    'confirmed' => 'sudah dikonfirmasikan',
                    'pending' => 'sedang menunggu approval dosen',
                    'approved_dosen' => 'sedang menunggu approval teknisi',
                    'approved_teknisi' => 'sedang menunggu approval Ka Lab',
                    default => 'sedang diproses',
                };

                return [
                    'available' => false,
                    'conflict_type' => 'booking',
                    'conflict_info' => "Bentrok dengan booking oleh {$booking->user->name} yang {$statusLabel}",
                ];
            }
        }

        return [
            'available' => true,
            'conflict_type' => null,
            'conflict_info' => null,
        ];
    }

    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'pending' => 'Menunggu persetujuan dosen.',
            'approved_dosen' => 'Disetujui dosen, menunggu teknisi.',
            'approved_teknisi' => 'Disetujui teknisi, menunggu Ka Lab.',
            'confirmed' => 'Booking dikonfirmasikan! ✅',
            'rejected' => 'Booking ditolak.',
            default => '',
        };
    }

    public static function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'approved_dosen' => 'bg-blue-100 text-blue-800 border-blue-300',
            'approved_teknisi' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
            'confirmed' => 'bg-green-100 text-green-800 border-green-300',
            'rejected' => 'bg-red-100 text-red-800 border-red-300',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public static function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Menunggu Persetujuan',
            'approved_dosen' => 'Disetujui Dosen',
            'approved_teknisi' => 'Disetujui Teknisi',
            'confirmed' => 'Dikonfirmasi ✅',
            'rejected' => 'Ditolak ❌',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }
}
