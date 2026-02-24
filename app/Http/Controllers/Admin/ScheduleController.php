<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lab;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * Display schedule management page
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'supervisor']);
        
        // Filter by lab
        if ($request->filled('lab')) {
            $query->where('lab_name', $request->lab);
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
        
        // Search by user name or email
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $bookings = $query->orderBy('booking_date', 'desc')
                         ->orderBy('start_time', 'asc')
                         ->paginate(20);
        
        $labs = Lab::where('status', 'active')->orderBy('name')->pluck('name', 'name');
        
        return view('admin.schedule.index', compact('bookings', 'labs'));
    }

    /**
     * Show booking detail
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'supervisor']);
        return view('admin.schedule.show', compact('booking'));
    }

    /**
     * Update booking status (Admin can override any status)
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'pending', 'approved_dosen', 'approved_teknisi', 
                'approved_kalab', 'confirmed', 'rejected', 'cancelled'
            ])],
            'admin_note' => 'nullable|string|max:500',
        ]);

        $oldStatus = $booking->status;
        
        // Build notes with admin annotation
        $newNotes = $booking->notes;
        if (!empty($validated['admin_note'])) {
            $timestamp = now()->format('Y-m-d H:i');
            $newNotes .= ($newNotes ? "\n" : "") . "[Admin {$timestamp}: {$validated['admin_note']}]";
        }
        
        $booking->update([
            'status' => $validated['status'],
            'notes' => $newNotes,
        ]);

        // Log status change
        \Log::info('Admin updated booking status', [
            'booking_id' => $booking->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'admin_id' => auth()->id(),
            'admin_note' => $validated['admin_note'],
        ]);

        return back()->with('success', "✅ Status booking diubah dari '{$oldStatus}' menjadi '{$validated['status']}'");
    }

    /**
     * Cancel booking
     */
    public function cancel(Booking $booking)
    {
        // Prevent cancelling already cancelled/rejected bookings
        if (in_array($booking->status, ['cancelled', 'rejected'])) {
            return back()->with('error', '❌ Booking ini sudah dibatalkan/ditolak sebelumnya.');
        }

        $booking->update([
            'status' => 'cancelled',
            'rejected_at' => now(),
            'rejection_reason' => 'Dibatalkan oleh Admin',
        ]);

        \Log::info('Admin cancelled booking', [
            'booking_id' => $booking->id,
            'admin_id' => auth()->id(),
        ]);

        return back()->with('success', '🗑️ Booking berhasil dibatalkan');
    }

    /**
     * Show calendar view
     */
    public function calendar(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = Carbon::today()->toDateString();
        }
        
        $bookings = Booking::with('user')
            ->whereDate('booking_date', $date)
            ->where('status', 'confirmed')
            ->orderBy('start_time')
            ->get();
        
        $labs = Lab::where('status', 'active')->orderBy('name')->get();
        
        return view('admin.schedule.calendar', compact('bookings', 'labs', 'date'));
    }

    /**
     * Get available slots for a lab (AJAX)
     * ✅ FIX: Flexible time matching for time range bookings
     */
    public function availableSlots(Request $request)
    {
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
                // Session is booked if it overlaps with any confirmed booking
                if ($sessStart < $dbEnd && $sessEnd > $dbStart) {
                    $isBooked = true;
                    $bookingInfo = "Terisi oleh booking ({$dbStart} - {$dbEnd})";
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
}