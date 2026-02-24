<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display booking dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();

        return match($user->role) {
            'mahasiswa' => $this->mahasiswaView(),
            'dosen' => $this->dosenView(),
            'teknisi' => $this->teknisiView(),
            'ketua_lab' => $this->kalabView(),
            'admin' => $this->adminView(),
            default => redirect()->route('dashboard'),
        };
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

        $booking = Booking::create([
            'user_id' => $user->id,
            'lab_name' => $validated['lab_name'],
            'session' => $validated['session'],
            'start_time' => $validated['start_time_custom'] ?? ($validated['start_time'] ?? null),
            'end_time' => $validated['end_time_custom'] ?? ($validated['end_time'] ?? null),
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

        Log::info('Booking created', [
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
     * Approve booking by Ka Lab (Final approval)
     */
    public function approveByKalab(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->role !== 'ketua_lab' && $user->role !== 'admin') {
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
        ]);

        return back()->with('success', '🎉 Booking BERHASIL DIKONFIRMASI! Silakan gunakan lab sesuai jadwal.');
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

    /**
     * Print booking form (A4 size) - Only for admin, teknisi, and kalab
     */
    public function printForm(Booking $booking)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'teknisi', 'ketua_lab'])) {
            abort(403, 'Anda tidak berwenang mencetak formulir ini');
        }
        
        $approvalDate = $booking->approved_at_kalab ?? now();
        
        return view('booking.print-form', compact('booking', 'approvalDate'));
    }

    /**
     * Download booking form as PDF
     */
    public function downloadPDF(Booking $booking)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'teknisi', 'ketua_lab'])) {
            abort(403, 'Anda tidak berwenang mengunduh formulir ini');
        }
        
        $approvalDate = $booking->approved_at_kalab ?? now();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('booking.print-form', compact('booking', 'approvalDate'));
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'Form-Peminjaman-' . str_replace(' ', '-', $booking->lab_name) . '-' . $booking->id . '.pdf';
        
        return $pdf->download($filename);
    }

    // ========================================================================
    // ROLE-SPECIFIC VIEW METHODS
    // ========================================================================

    private function mahasiswaView()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
        $pendingCount = Booking::where('user_id', $user->id)->where('status', 'pending')->count();
        return view('booking.mahasiswa', compact('bookings', 'pendingCount', 'user'));
    }

    private function dosenView()
    {
        $user = Auth::user();
        
        $ownBookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $pendingApprovals = Booking::whereHas('user', fn($q) => $q->where('role', 'mahasiswa'))
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        $stats = [
            'total' => Booking::where('user_id', $user->id)->count(),
            'pending' => Booking::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => Booking::where('user_id', $user->id)->where('status', 'confirmed')->count(),
            'awaiting_approval' => Booking::whereHas('user', fn($q) => $q->where('role', 'mahasiswa'))
                ->where('status', 'pending')->count(),
        ];

        return view('booking.dosen', compact('ownBookings', 'pendingApprovals', 'stats', 'user'));
    }

    /**
     * View for Teknisi - Filter by lab assignment
     */
    private function teknisiView()
    {
        $user = Auth::user();
        
        $pendingApprovals = Booking::whereIn('status', ['pending', 'approved_dosen'])
            ->where('lab_name', $user->lab_name)
            ->orderBy('created_at', 'asc')
            ->paginate(10);
            
        $allBookings = Booking::where('lab_name', $user->lab_name)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $stats = [
            'awaiting_approval' => Booking::where('lab_name', $user->lab_name)
                ->whereIn('status', ['pending', 'approved_dosen'])
                ->count(),
            'total_confirmed' => Booking::where('lab_name', $user->lab_name)
                ->where('status', 'confirmed')
                ->count(),
            'total_rejected' => Booking::where('lab_name', $user->lab_name)
                ->where('status', 'rejected')
                ->count(),
            'lab_name' => $user->lab_name,
        ];
        
        return view('booking.teknisi', compact('pendingApprovals', 'allBookings', 'stats', 'user'));
    }

    /**
     * View for Ketua Lab
     */
    private function kalabView()
    {
        $user = Auth::user();
        
        $pendingApprovals = Booking::where('status', 'approved_teknisi')
            ->orderBy('created_at', 'asc')
            ->paginate(10);
            
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->orderBy('booking_date', 'desc')
            ->paginate(20);
            
        $stats = [
            'awaiting_final' => Booking::where('status', 'approved_teknisi')->count(),
            'confirmed_today' => Booking::where('status', 'confirmed')
                ->whereDate('booking_date', today())
                ->count(),
            'total_confirmed' => Booking::where('status', 'confirmed')->count(),
        ];
        
        return view('booking.kalab', compact('pendingApprovals', 'confirmedBookings', 'stats', 'user'));
    }

    /**
     * View for Admin
     */
    private function adminView()
    {
        $user = Auth::user();
        
        $allBookings = Booking::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'rejected' => Booking::where('status', 'rejected')->count(),
        ];
        
        return view('booking.admin', compact('allBookings', 'stats', 'user'));
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function getStatusMessage(string $status): string
    {
        return match($status) {
            'pending' => 'Menunggu persetujuan dosen.',
            'approved_dosen' => 'Disetujui dosen, menunggu teknisi.',
            'approved_teknisi' => 'Disetujui teknisi, menunggu Ka Lab.',
            'confirmed' => 'Booking dikonfirmasi! ✅',
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