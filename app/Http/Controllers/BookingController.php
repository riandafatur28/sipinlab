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
    // ========================================================================
    // 📊 INDEX: Dashboard Booking (Role-Based dengan Dual Role Support)
    // ========================================================================
    public function index(Request $request)
    {
        $user = Auth::user();

        // ✅ Ambil $labs SEKARANG (sebelum branching) agar selalu tersedia untuk view
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

        // ====================================================================
        // 🎓 MAHASISWA: Hanya lihat booking sendiri
        // ====================================================================
        if ($user->isMahasiswa()) {
            $bookings = Booking::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $stats = [
                'total' => $bookings->total(),
                'pending' => Booking::where('user_id', $user->id)->where('status', 'pending')->count(),
                'confirmed' => Booking::where('user_id', $user->id)->where('status', 'confirmed')->count(),
                'rejected' => Booking::where('user_id', $user->id)->where('status', 'rejected')->count(),
            ];

            return view('booking.index', compact('bookings', 'stats', 'labs'));
        }

        // ====================================================================
        // 👨‍🏫 DOSEN (termasuk yang juga Kalab):
        // - Lihat booking sendiri + approval section untuk booking mahasiswa
        // ====================================================================
        if ($user->isDosen()) {
            // Booking yang dibuat user ini
            $myBookingsQuery = Booking::where('user_id', $user->id);

            // Booking mahasiswa yang menunggu persetujuan dosen
            $pendingApprovalsQuery = Booking::where('status', 'pending')
                ->whereHas('user', function($q) {
                    $q->where('role', 'mahasiswa');
                });

            // Tab navigation support via URL parameter
            $tab = $request->get('tab', 'mybookings');

            if ($tab === 'approvals') {
                $bookings = $pendingApprovalsQuery->orderBy('created_at', 'desc')->paginate(10);
                $stats = [
                    'total' => $pendingApprovalsQuery->count(),
                    'pending' => $pendingApprovalsQuery->count(),
                    'confirmed' => 0,
                    'rejected' => 0,
                ];
            } elseif ($tab === 'management' && $user->isKalab()) {
                // Jika Dosen juga Kalab dan pilih tab management
                return $this->indexKalabManagement($request, $labs);
            } else {
                // Default: tampilkan booking saya
                $bookings = $myBookingsQuery->orderBy('created_at', 'desc')->paginate(10);
                $stats = [
                    'total' => $myBookingsQuery->count(),
                    'pending' => (clone $myBookingsQuery)->where('status', 'pending')->count(),
                    'confirmed' => (clone $myBookingsQuery)->where('status', 'confirmed')->count(),
                    'rejected' => (clone $myBookingsQuery)->where('status', 'rejected')->count(),
                ];
            }

            return view('booking.index', compact('bookings', 'stats', 'labs', 'tab'));
        }

        // ====================================================================
        // 👔 KALAB (yang bukan dosen): Dashboard management penuh
        // ====================================================================
        if ($user->isKalab()) {
            return $this->indexKalabManagement($request, $labs);
        }

        // ====================================================================
        // 🔧 TEKNISI / 👨‍💼 ADMIN: Management booking dengan filter
        // ====================================================================
        return $this->indexStaffManagement($request, $user, $labs);
    }

    // ========================================================================
    // 🔧 HELPER: Kalab Management View
    // ========================================================================
    private function indexKalabManagement(Request $request, array $labs)
    {
        $query = Booking::with(['user'])->orderBy('created_at', 'desc');

        // Filter search
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nim', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Filter lab
        if ($request->filled('lab')) {
            $query->where('lab_name', $request->lab);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter date
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

        $stats = [
            'total_booking' => (clone $query)->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'approved_dosen', 'approved_teknisi'])->count(),
            'hari_ini' => (clone $query)->whereDate('booking_date', today())->count(),
        ];

        $bookings = $query->paginate(10)->withQueryString();

        return view('booking.index', compact('bookings', 'stats', 'labs'));
    }

    // ========================================================================
    // 🔧 HELPER: Staff Management View (Teknisi/Admin)
    // ========================================================================
    private function indexStaffManagement(Request $request, $user, array $labs)
    {
        $query = Booking::with(['user'])->orderBy('created_at', 'desc');

        // Teknisi hanya lihat lab sendiri
        if ($user->isTeknisi() && !empty($user->lab_name)) {
            $query->where('lab_name', $user->lab_name);
        } elseif ($request->filled('default_lab')) {
            $query->where('lab_name', $request->default_lab);
        }

        // Filter search
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('nim', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Filter lab
        if ($request->filled('lab')) {
            $query->where('lab_name', $request->lab);
        }

        // Filter date
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

        // Filter status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $stats = [
            'total_booking' => (clone $query)->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'approved_dosen'])->count(),
            'hari_ini' => (clone $query)->whereDate('booking_date', today())->count(),
        ];

        $bookings = $query->paginate(10)->withQueryString();

        return view('booking.index', compact('bookings', 'stats', 'labs'));
    }

    // ========================================================================
    // 🗑️ DESTROY: Hapus booking (Admin/Kalab/Teknisi only)
    // ========================================================================
    public function destroy(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isKalab() && $user->role !== 'ketua_lab') {
            abort(403, 'Anda tidak berwenang menghapus booking ini');
        }

        if ($user->isTeknisi() && $user->lab_name !== $booking->lab_name) {
            abort(403, 'Anda tidak dapat menghapus booking untuk laboratorium ini');
        }

        if ($booking->status === 'confirmed') {
            return back()->withErrors(['error' => '⚠️ Booking sudah dikonfirmasi. Hubungi admin untuk pembatalan.']);
        }

        Log::info('Booking deleted', [
            'booking_id' => $booking->id,
            'deleted_by' => $user->id,
            'role' => $user->role,
        ]);

        $booking->delete();

        return redirect()->back()->with('success', '✅ Booking berhasil dihapus!');
    }

    // ========================================================================
    // 📄 PDF & PRINT METHODS
    // ========================================================================

    public function downloadFormAfterApproved(Booking $booking)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isKalab() && $user->role !== 'ketua_lab') {
            abort(403, 'Anda tidak berwenang mengunduh formulir');
        }

        if ($booking->status !== 'confirmed') {
            return back()->withErrors(['error' => 'Booking belum dikonfirmasi, formulir belum tersedia']);
        }

        $approvalDate = $booking->approved_at_kalab ?? now();
        $pdf = Pdf::loadView('booking.form-approved', compact('booking', 'approvalDate'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Form-Booking-' . str_replace(' ', '-', $booking->lab_name) . '-' . $booking->id . '-' . Carbon::parse($booking->booking_date)->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    public function printForm(Booking $booking)
    {
        $user = Auth::user();

        if ($booking->user_id !== $user->id && !$user->canViewAllBookings()) {
            abort(403, 'Anda tidak berwenang mencetak formulir ini');
        }

        $approvalDate = $booking->approved_at_kalab ?? now();
        return view('booking.print-form', compact('booking', 'approvalDate'));
    }

    public function downloadPDF(Booking $booking)
    {
        $user = Auth::user();

        if ($booking->user_id !== $user->id && !$user->canViewAllBookings()) {
            abort(403, 'Anda tidak berwenang mengunduh formulir ini');
        }

        $approvalDate = $booking->approved_at_kalab ?? now();
        $pdf = Pdf::loadView('booking.print-form', compact('booking', 'approvalDate'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Form-Peminjaman-' . str_replace(' ', '-', $booking->lab_name) . '-' . $booking->id . '.pdf';
        return $pdf->download($filename);
    }

    // ========================================================================
    // ✅ APPROVAL METHODS
    // ========================================================================

    public function approveByKalab(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if (!$user->canApproveAsKalab()) {
            abort(403, 'Hanya Ketua Lab atau Admin yang dapat melakukan konfirmasi final');
        }

        if ($booking->status !== 'approved_teknisi') {
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

    public function approveByDosen(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if (!$user->isDosen()) {
            abort(403, 'Hanya dosen yang dapat menyetujui booking mahasiswa');
        }

        if ($booking->status !== 'pending') {
            return back()->withErrors(['error' => 'Booking ini tidak dapat disetujui']);
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

    public function approveByTeknisi(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if (!$user->isTeknisi() || $user->lab_name !== $booking->lab_name) {
            abort(403, 'Anda tidak berwenang menyetujui booking untuk laboratorium ini');
        }

        if (!in_array($booking->status, ['approved_dosen', 'pending'])) {
            return back()->withErrors(['error' => 'Booking belum siap disetujui teknisi']);
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

    // ========================================================================
    // ❌ REJECT & CANCEL
    // ========================================================================

    public function reject(Request $request, Booking $booking)
    {
        $validated = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $user = Auth::user();

        if (!$user->canApproveBookings()) {
            abort(403, 'Unauthorized');
        }

        if (in_array($booking->status, ['confirmed', 'rejected', 'cancelled'])) {
            return back()->withErrors(['error' => 'Booking sudah tidak dapat ditolak']);
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

    public function cancel(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($booking->user_id !== $user->id) {
            abort(403, 'Hanya pembuat booking yang dapat membatalkan');
        }

        if ($booking->status === 'confirmed') {
            return back()->withErrors(['error' => 'Booking sudah dikonfirmasi, hubungi admin untuk pembatalan']);
        }

        $booking->update([
            'status' => 'cancelled',
            'rejected_at' => now(),
            'rejection_reason' => 'Dibatalkan oleh pemohon',
        ]);

        return back()->with('success', '🗑️ Booking berhasil dibatalkan');
    }

    // ========================================================================
    // 🎓 CREATE: Form Booking untuk MAHASISWA
    // ========================================================================
    public function create(Request $request)
    {
        $user = Auth::user();

        // ✅ Redirect jika bukan mahasiswa
        if (!$user->isMahasiswa()) {
            return redirect()->route('booking.create-dosen')
                ->with('info', 'Anda diarahkan ke form booking dosen.');
        }

        $prefilled = [
            'lab_name' => $request->get('lab'),
            'session' => $request->get('session'),
            'booking_date' => $request->get('date'),
        ];

        $dosens = User::where('role', 'dosen')->orderBy('name')->get(['id', 'name', 'email']);
        $students = User::mahasiswa()->orderBy('name')->get(['id', 'name', 'email', 'nim']);
        $labs = $this->getAvailableLabs();
        $activities = $this->getMahasiswaActivities();

        return view('booking.create-mahasiswa', compact(
            'user', 'dosens', 'students', 'labs', 'activities', 'prefilled'
        ));
    }

    // ========================================================================
    // 👨‍🏫 CREATE DOSEN: Form Booking khusus DOSEN
    // ========================================================================
    public function createDosen(Request $request)
    {
        $user = Auth::user();

        // ✅ Redirect jika bukan dosen
        if (!$user->isDosen()) {
            return redirect()->route('booking.create')
                ->with('info', 'Anda diarahkan ke form booking mahasiswa.');
        }

        $prefilled = [
            'lab_name' => $request->get('lab'),
            'session' => $request->get('session'),
            'booking_date' => $request->get('date'),
        ];

        $students = User::mahasiswa()->orderBy('name')->get(['id', 'name', 'email', 'nim']);
        $labs = $this->getAvailableLabs();
        $activities = $this->getDosenActivities();

        return view('booking.create-dosen', compact(
            'user', 'students', 'labs', 'activities', 'prefilled'
        ));
    }

    // ========================================================================
    // 💾 STORE: Simpan Booking (Handle Kedua Role)
    // ========================================================================
    public function store(Request $request)
    {
        $user = Auth::user();
        $isMahasiswa = $user->isMahasiswa();

        try {
            // ✅ VALIDASI BERDASARKAN ROLE
            if ($isMahasiswa) {
                $validated = $request->validate($this->mahasiswaRules(), $this->validationMessages());
            } else {
                $validated = $request->validate($this->dosenRules(), $this->validationMessages());
            }

            // ✅ CEK KONFLIK JADWAL
            $startTime = $validated['start_time_custom'] ?? ($validated['start_time'] ?? '07:00');
            $endTime = $validated['end_time_custom'] ?? ($validated['end_time'] ?? '08:00');

            $conflict = $this->checkTimeSlotConflict(
                $validated['lab_name'],
                $validated['booking_date'],
                $startTime,
                $endTime
            );

            if (!$conflict['available']) {
                return back()->withInput()->withErrors([
                    'booking_date' => "❌ Waktu tidak tersedia. {$conflict['conflict_info']}",
                ]);
            }

            // ✅ TENTUKAN STATUS AWAL
            $status = $isMahasiswa ? 'pending' : 'approved_dosen';

            // ✅ UPDATE DATA USER (jika ada perubahan)
            $this->updateUserData($user, $validated, $isMahasiswa);

            // ✅ HITUNG TANGGAL RANGE
            $bookingDate = Carbon::parse($validated['booking_date']);
            $startDate = $bookingDate->copy();
            $endDate = $bookingDate->copy()->addDays($validated['duration_days'] - 1);

            // ✅ CREATE BOOKING
            $booking = Booking::create([
                'user_id' => $user->id,
                'lab_name' => $validated['lab_name'],
                'session' => $validated['session'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'booking_date' => $validated['booking_date'],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $validated['duration_days'],
                'activity' => $this->resolveActivity($validated),
                'purpose' => $validated['purpose'],
                'phone' => $validated['phone'],
                'prodi' => $validated['prodi'] ?? $user->prodi ?? 'Teknik Informatika',
                'golongan' => $validated['golongan'] ?? $user->golongan ?? '-',
                'is_group' => $validated['with_students'] ?? $validated['is_group'] ?? false,
                'supervisor_id' => $validated['supervisor_id'] ?? null,
                'notes' => $validated['equipment_needs'] ?? $request->input('notes'),
                'status' => $status,
                // Auto-approve untuk dosen
                'approved_by_dosen' => !$isMahasiswa ? $user->id : null,
                'approved_at_dosen' => !$isMahasiswa ? now() : null,
            ]);

            // ✅ SYNC ANGGOTA KELOMPOK (mahasiswa only)
            if ($isMahasiswa && !empty($validated['members'])) {
                $booking->members()->sync($validated['members']);
            }

            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'role' => $user->role,
                'lab' => $validated['lab_name'],
                'status' => $status,
            ]);

            $message = $isMahasiswa
                ? '✅ Booking berhasil diajukan! Menunggu persetujuan: Dosen → Teknisi → Ka Lab.'
                : '✅ Booking berhasil diajukan! Menunggu konfirmasi Teknisi.';

            return redirect()->route('booking.index')->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Booking store failed', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => '❌ Gagal: ' . $e->getMessage()]);
        }
    }

    // ========================================================================
    // 🔍 SEARCH USERS (AJAX)
    // ========================================================================
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

        return response()->json(
            $users->limit(10)->get(['id', 'name', 'email', 'role', 'nim', 'nip'])
        );
    }

    // ========================================================================
    // 👁️ SHOW: Detail Booking
    // ========================================================================
    public function show(Booking $booking)
    {
        $user = Auth::user();

        // Authorization: owner atau staff
        if ($booking->user_id !== $user->id && !$user->canViewAllBookings()) {
            abort(403, 'Unauthorized');
        }

        return view('booking.show', compact('booking'));
    }

    // ========================================================================
    // 🔧 HELPER METHODS (PRIVATE)
    // ========================================================================

    private function getAvailableLabs(): array
    {
        $labs = Lab::where('status', 'active')->orderBy('name')->pluck('name')->toArray();
        return $labs ?: [
            'Multimedia Cerdas (MMC)',
            'Komputasi dan Sistem Jaringan (KSI)',
            'Arsitektur dan Jaringan Komputer (AJK)',
            'Mobile',
            'Rekayasa Perangkat Lunak (RPL)',
        ];
    }

    private function getMahasiswaActivities(): array
    {
        return [
            'Tugas Kuliah',
            'Tugas Akhir',
            'Praktikum',
            'Penelitian',
            'Lomba/Kompetisi',
            'Kegiatan Komunitas',
            'Lainnya',
        ];
    }

    private function getDosenActivities(): array
    {
        return [
            'Bimbingan Tugas Akhir / Skripsi',
            'Praktikum Mata Kuliah',
            'Penelitian / Riset',
            'Pengabdian Masyarakat',
            'Workshop / Seminar',
            'Ujian / Evaluasi',
            'Rapat Koordinasi Prodi',
            'Lainnya',
        ];
    }

    private function mahasiswaRules(): array
    {
        return [
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
            'golongan' => 'required|in:A,B,C',
            'is_group' => 'nullable|boolean',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'agreement' => 'accepted|required',
        ];
    }

    private function dosenRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|min:10|max:20',
            'phone' => 'required|string|max:20',
            'lab_name' => 'required|string|max:255',
            'session' => 'required|string|max:100',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
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
        ];
    }

    private function validationMessages(): array
    {
        return [
            'agreement.accepted' => 'Anda harus menyetujui pernyataan tanggung jawab',
            'nip.required' => 'NIP wajib diisi',
            'name.required' => 'Nama lengkap wajib diisi',
            'phone.required' => 'No. Telepon wajib diisi',
            'lab_name.required' => 'Laboratorium wajib dipilih',
            'booking_date.required' => 'Tanggal wajib dipilih',
            'activity.required' => 'Jenis kegiatan wajib dipilih',
            'purpose.required' => 'Deskripsi keperluan wajib diisi',
        ];
    }

    private function updateUserData(User $user, array $validated, bool $isMahasiswa): void
    {
        $updateData = [];

        if ($isMahasiswa) {
            if (isset($validated['phone']) && $user->phone !== $validated['phone']) {
                $updateData['phone'] = $validated['phone'];
            }
        } else {
            // Dosen: update name, nip, phone jika berbeda
            if (isset($validated['name']) && $user->name !== $validated['name']) {
                $updateData['name'] = $validated['name'];
            }
            if (isset($validated['nip']) && $user->nip !== $validated['nip']) {
                $updateData['nip'] = $validated['nip'];
            }
            if (isset($validated['phone']) && $user->phone !== $validated['phone']) {
                $updateData['phone'] = $validated['phone'];
            }
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }

    private function resolveActivity(array $validated): string
    {
        if (($validated['activity'] ?? '') === 'Lainnya') {
            return $validated['activity_other'] ?? 'Lainnya';
        }
        return $validated['activity'];
    }

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
                    'conflict_info' => "Bentrok jadwal kuliah: {$schedule->course_name} (Gol. {$schedule->golongan})",
                ];
            }
        }

        // Check Other Bookings
        $blockingStatuses = ['confirmed', 'pending', 'approved_dosen', 'approved_teknisi'];
        $conflictingBookings = Booking::where('lab_name', $labName)
            ->whereDate('booking_date', $bookingDate)
            ->whereIn('status', $blockingStatuses)
            ->where('id', '!=', request()->route('booking')?->id ?? 0)
            ->with('user')
            ->get();

        foreach ($conflictingBookings as $booking) {
            $bStart = substr($booking->start_time ?? '', 0, 5);
            $bEnd = substr($booking->end_time ?? '', 0, 5);
            if (empty($bStart) || empty($bEnd)) continue;

            if ($startTime < $bEnd && $endTime > $bStart) {
                $statusLabel = match($booking->status) {
                    'confirmed' => 'sudah dikonfirmasi',
                    'pending' => 'menunggu approval dosen',
                    'approved_dosen' => 'menunggu approval teknisi',
                    'approved_teknisi' => 'menunggu approval Ka Lab',
                    default => 'sedang diproses',
                };
                return [
                    'available' => false,
                    'conflict_type' => 'booking',
                    'conflict_info' => "Bentrok booking oleh {$booking->user->name} yang {$statusLabel}",
                ];
            }
        }

        return ['available' => true, 'conflict_type' => null, 'conflict_info' => null];
    }

    // ========================================================================
    // 🏷️ STATUS HELPERS (Static)
    // ========================================================================

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
