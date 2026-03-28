<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lab;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    /**
     * Display schedule management page (Admin/Kalab/Teknisi)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query with eager loading
        $query = Booking::with(['user', 'supervisor']);

        // ✅ Scope data untuk Kalab & Teknisi: hanya booking lab yang ditugaskan
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            $query->where('lab_name', $user->lab_name);
        }

        // Filter by lab (Admin/Kalab/Teknisi tanpa lab_name bisa filter manual)
        if ($request->filled('lab')) {
            // Kalab/Teknisi dengan lab_name hanya bisa filter lab sendiri
            if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
                if ($request->lab === $user->lab_name) {
                    $query->where('lab_name', $request->lab);
                }
                // Jika filter lab != lab sendiri, silently ignore (tetap pakai scope)
            } else {
                $query->where('lab_name', $request->lab);
            }
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('booking_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('booking_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by user name, email, NIM, or NIP
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('nim', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Get bookings with pagination
        $bookings = $query->orderBy('booking_date', 'desc')
                         ->orderBy('start_time', 'asc')
                         ->paginate(20)
                         ->withQueryString();

        // Get labs for filter dropdown
        $labsQuery = Lab::where('status', 'active')->orderBy('name');

        // Scope labs for Kalab/Teknisi
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            $labsQuery->where('name', $user->lab_name);
        }

        $labs = $labsQuery->pluck('name', 'name');

        // Stats for dashboard cards
        $stats = [
            'total_booking' => (clone $query)->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'approved_dosen', 'approved_teknisi'])->count(),
            'hari_ini' => (clone $query)->whereDate('booking_date', today())->count(),
        ];

        return view('admin.schedule.index', compact('bookings', 'labs', 'stats'));
    }

    /**
     * Show booking detail (Admin/Kalab/Teknisi)
     */
    public function show(Booking $booking)
    {
        $user = Auth::user();

        // ✅ Authorization: Kalab/Teknisi hanya bisa lihat booking lab sendiri
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            if ($booking->lab_name !== $user->lab_name) {
                abort(403, 'Anda tidak memiliki akses ke booking laboratorium ini.');
            }
        }

        $booking->load(['user', 'supervisor']);

        // Load related data for detail view
        $lab = Lab::where('name', $booking->lab_name)->first();

        return view('admin.schedule.show', compact('booking', 'lab'));
    }

    /**
     * Update booking status (Admin/Kalab/Teknisi - dengan batasan role)
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $user = Auth::user();

        // ✅ Authorization: Kalab/Teknisi hanya bisa update booking lab sendiri
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            if ($booking->lab_name !== $user->lab_name) {
                abort(403, 'Anda tidak memiliki akses untuk mengupdate booking ini.');
            }
        }

        // ✅ Validasi status yang diizinkan berdasarkan role
        if ($user->isTeknisi()) {
            // Teknisi: tidak bisa set ke approved_kalab atau confirmed langsung
            $allowedStatuses = ['pending', 'approved_dosen', 'approved_teknisi', 'rejected', 'cancelled'];
        } else {
            // Admin/Kalab: semua status diizinkan
            $allowedStatuses = [
                'pending', 'approved_dosen', 'approved_teknisi',
                'approved_kalab', 'confirmed', 'rejected', 'cancelled'
            ];
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
            'admin_note' => 'nullable|string|max:500',
        ]);

        $oldStatus = $booking->status;

        // Build notes with annotation
        $newNotes = $booking->notes;
        if (!empty($validated['admin_note'])) {
            $timestamp = now()->format('Y-m-d H:i');
            $role = $user->role === 'admin' ? 'Admin' : ($user->isKalab() ? 'Kalab' : 'Teknisi');
            $newNotes .= ($newNotes ? "\n" : "") . "[{$role} {$timestamp}: {$validated['admin_note']}]";
        }

        // ✅ Auto-set approval timestamps based on status change
        $updateData = [
            'status' => $validated['status'],
            'notes' => $newNotes,
        ];

        if ($validated['status'] === 'approved_dosen' && empty($booking->approved_at_dosen)) {
            $updateData['approved_by_dosen'] = $user->id;
            $updateData['approved_at_dosen'] = now();
        }
        if ($validated['status'] === 'approved_teknisi' && empty($booking->approved_at_teknisi)) {
            $updateData['approved_by_teknisi'] = $user->id;
            $updateData['approved_at_teknisi'] = now();
        }
        if ($validated['status'] === 'approved_kalab' && empty($booking->approved_at_kalab)) {
            $updateData['approved_by_kalab'] = $user->id;
            $updateData['approved_at_kalab'] = now();
        }
        if ($validated['status'] === 'confirmed' && empty($booking->approved_at_kalab)) {
            $updateData['approved_by_kalab'] = $user->id;
            $updateData['approved_at_kalab'] = now();
        }
        if (in_array($validated['status'], ['rejected', 'cancelled'])) {
            $updateData['rejected_by'] = $user->id;
            $updateData['rejected_at'] = now();
            if ($validated['status'] === 'rejected' && empty($booking->rejection_reason)) {
                $updateData['rejection_reason'] = $validated['admin_note'] ?? 'Ditolak oleh ' . ($user->isKalab() ? 'Kalab' : ($user->isTeknisi() ? 'Teknisi' : 'Admin'));
            }
        }

        $booking->update($updateData);

        // Log status change
        Log::info('Booking status updated', [
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'updated_by' => $user->id,
            'user_role' => $user->role,
            'lab_name' => $booking->lab_name,
            'admin_note' => $validated['admin_note'],
        ]);

        return back()->with('success', "✅ Status booking diubah dari '{$oldStatus}' menjadi '{$validated['status']}'");
    }

    /**
     * Cancel booking (Admin/Kalab only - Teknisi tidak bisa cancel)
     */
    public function cancel(Booking $booking)
    {
        $user = Auth::user();

        // ✅ Authorization: Kalab hanya bisa cancel booking lab sendiri
        if ($user->isKalab() && !empty($user->lab_name)) {
            if ($booking->lab_name !== $user->lab_name) {
                abort(403, 'Anda tidak memiliki akses untuk membatalkan booking ini.');
            }
        }

        // ✅ Teknisi tidak boleh cancel booking (hanya Admin/Kalab)
        if ($user->isTeknisi()) {
            abort(403, 'Hanya Admin atau Ka Lab yang dapat membatalkan booking.');
        }

        // Prevent cancelling already cancelled/rejected bookings
        if (in_array($booking->status, ['cancelled', 'rejected'])) {
            return back()->with('error', '❌ Booking ini sudah dibatalkan/ditolak sebelumnya.');
        }

        $booking->update([
            'status' => 'cancelled',
            'rejected_at' => now(),
            'rejection_reason' => 'Dibatalkan oleh ' . ($user->isKalab() ? 'Kalab' : 'Admin'),
        ]);

        Log::info('Booking cancelled', [
            'booking_id' => $booking->id,
            'cancelled_by' => $user->id,
            'user_role' => $user->role,
            'lab_name' => $booking->lab_name,
        ]);

        return back()->with('success', '🗑️ Booking berhasil dibatalkan');
    }

    /**
     * Show calendar view (Admin/Kalab/Teknisi)
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        $date = $request->get('date', Carbon::today()->toDateString());

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = Carbon::today()->toDateString();
        }

        // Base query for bookings
        $bookingsQuery = Booking::with('user')
            ->whereDate('booking_date', $date)
            ->where('status', 'confirmed');

        // ✅ Scope for Kalab/Teknisi
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            $bookingsQuery->where('lab_name', $user->lab_name);
        }

        $bookings = $bookingsQuery->orderBy('start_time')->get();

        // Get labs for filter
        $labsQuery = Lab::where('status', 'active')->orderBy('name');
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            $labsQuery->where('name', $user->lab_name);
        }
        $labs = $labsQuery->get();

        return view('admin.schedule.calendar', compact('bookings', 'labs', 'date'));
    }

    /**
     * Get available slots for a lab (AJAX) - Admin/Kalab/Teknisi
     * ✅ FIX: Flexible time matching for time range bookings
     */
    public function availableSlots(Request $request)
    {
        $user = Auth::user();
        $labName = $request->get('lab');
        $date = $request->get('date');

        // Validate input
        if (!$labName || !$date) {
            return response()->json(['error' => 'Missing required parameters'], 400);
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // ✅ Authorization: Kalab/Teknisi hanya bisa cek slot lab sendiri
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            if ($labName !== $user->lab_name) {
                return response()->json(['error' => 'Unauthorized lab access'], 403);
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

        // Get confirmed bookings for this lab and date
        $confirmedBookings = Booking::where('lab_name', $labName)
            ->whereDate('booking_date', $date)
            ->where('status', 'confirmed')
            ->get(['start_time', 'end_time']);

        $available = [];
        foreach ($sessions as $session) {
            // Skip break time
            if ($session['is_break'] ?? false) {
                $available[] = [
                    'session' => $session['name'],
                    'start' => $session['start'],
                    'end' => $session['end'],
                    'available' => false,
                    'reason' => 'Istirahat',
                ];
                continue;
            }

            $isBooked = false;
            $bookingInfo = null;

            foreach ($confirmedBookings as $booking) {
                // ✅ Flexible time matching: handle '07:00' vs '07:00:00'
                $dbStart = substr($booking->start_time, 0, 5);
                $dbEnd = substr($booking->end_time, 0, 5);
                $sessStart = $session['start'];
                $sessEnd = $session['end'];

                // ✅ Check for time range overlap
                if ($sessStart < $dbEnd && $sessEnd > $dbStart) {
                    $isBooked = true;
                    $bookingInfo = "Terisi ({$dbStart} - {$dbEnd})";
                    break;
                }
            }

            $available[] = [
                'session' => $session['name'],
                'start' => $session['start'],
                'end' => $session['end'],
                'available' => !$isBooked,
                'reason' => $isBooked ? $bookingInfo : null,
            ];
        }

        return response()->json($available);
    }

    /**
     * ✅ AJAX: Get booking stats for dashboard widgets
     */
    public function getBookingStats(Request $request)
    {
        $user = Auth::user();

        // Base query
        $query = Booking::query();

        // ✅ Scope for Kalab/Teknisi
        if (($user->isKalab() || $user->isTeknisi()) && !empty($user->lab_name)) {
            $query->where('lab_name', $user->lab_name);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->date);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'pending' => (clone $query)->whereIn('status', ['pending', 'approved_dosen', 'approved_teknisi'])->count(),
            'rejected' => (clone $query)->whereIn('status', ['rejected', 'cancelled'])->count(),
            'today' => (clone $query)->whereDate('booking_date', today())->count(),
        ];

        return response()->json($stats);
    }

    /**
     * ✅ AJAX: Search users for booking assignment
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('query', '');
        $role = $request->get('role', 'mahasiswa');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('nim', 'like', "%{$query}%")
              ->orWhere('nip', 'like', "%{$query}%");
        });

        if ($role === 'mahasiswa') {
            $users->where('role', 'mahasiswa');
        } elseif ($role === 'dosen') {
            $users->where('role', 'dosen');
        }

        return response()->json(
            $users->limit(10)->get(['id', 'name', 'email', 'role', 'nim', 'nip'])
        );
    }
}
