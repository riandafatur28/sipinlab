<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\Booking;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LabManagementController extends Controller
{
    /**
     * Display list of labs (Admin/Kalab)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query
        $query = Lab::query();

        // ✅ Scope data untuk Kalab: hanya lab yang ditugaskan
        if ($user->role === 'kalab' && !empty($user->lab_name)) {
            $query->where('name', $user->lab_name);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Get labs with pagination (10 per page)
        $labs = $query->orderBy('name')->paginate(10)->withQueryString();

        // ✅ Return partial view if AJAX request (for live search)
        if ($request->ajax()) {
            return view('admin.labs.partials.table', compact('labs'))->render();
        }

        return view('admin.labs.index', compact('labs'));
    }

    /**
     * Show create form (Admin only)
     */
    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menambahkan laboratorium baru.');
        }
        return view('admin.labs.create');
    }

    /**
     * Store new lab (Admin only)
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menambahkan laboratorium baru.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:labs'],
            'code' => ['required', 'string', 'max:10', 'unique:labs', 'regex:/^[A-Z0-9]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'code.regex' => 'Kode lab hanya boleh berisi huruf besar dan angka.',
            'capacity.max' => 'Kapasitas maksimal 500 orang.',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        Lab::create($validated);

        Log::info('Lab created', [
            'lab_id' => Lab::latest()->first()->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.labs.index')
            ->with('success', '✅ Laboratorium <strong>' . e($validated['name']) . '</strong> berhasil ditambahkan!');
    }

    /**
     * Show lab detail (Admin/Kalab)
     */
    public function show(Lab $lab)
    {
        $user = Auth::user();

        if ($user->role === 'kalab' && !empty($user->lab_name) && $lab->name !== $user->lab_name) {
            abort(403, 'Anda tidak memiliki akses ke laboratorium ini.');
        }

        // ✅ Hitung stats yang benar
        $stats = [
            'total_bookings' => Booking::where('lab_name', $lab->name)->count(),
            'confirmed_bookings' => Booking::where('lab_name', $lab->name)
                ->where('status', 'confirmed')
                ->count(),
            'pending_bookings' => Booking::where('lab_name', $lab->name)
                ->whereIn('status', ['pending', 'approved_dosen', 'approved_teknisi'])
                ->count(),
            'active_schedules' => ClassSchedule::where('lab_name', $lab->name)
                ->where('status', 'active')
                ->count(),
            'today_bookings' => Booking::where('lab_name', $lab->name)
                ->whereDate('booking_date', today())
                ->count(),
        ];

        $recentBookings = Booking::where('lab_name', $lab->name)
            ->with('user')
            ->orderBy('booking_date', 'desc')
            ->take(5)
            ->get();

        return view('admin.labs.show', compact('lab', 'recentBookings', 'stats'));
    }

    /**
     * Show edit form (Admin/Kalab)
     */
    public function edit(Lab $lab)
    {
        $user = Auth::user();

        if ($user->role === 'kalab' && !empty($user->lab_name) && $lab->name !== $user->lab_name) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit laboratorium ini.');
        }

        return view('admin.labs.edit', compact('lab'));
    }

    /**
     * Update lab (Admin/Kalab)
     */
    public function update(Request $request, Lab $lab)
    {
        $user = Auth::user();

        if ($user->role === 'kalab' && !empty($user->lab_name) && $lab->name !== $user->lab_name) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate laboratorium ini.');
        }

        if ($user->role === 'kalab') {
            $validated = $request->validate([
                'description' => ['nullable', 'string', 'max:1000'],
                'location' => ['nullable', 'string', 'max:255'],
                'capacity' => ['required', 'integer', 'min:1', 'max:500'],
                'status' => ['required', 'in:active,inactive'],
            ]);
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:labs,name,' . $lab->id],
                'code' => ['required', 'string', 'max:10', 'unique:labs,code,' . $lab->id, 'regex:/^[A-Z0-9]+$/'],
                'description' => ['nullable', 'string', 'max:1000'],
                'location' => ['nullable', 'string', 'max:255'],
                'capacity' => ['required', 'integer', 'min:1', 'max:500'],
                'status' => ['required', 'in:active,inactive'],
            ], [
                'code.regex' => 'Kode lab hanya boleh berisi huruf besar dan angka.',
            ]);
            $validated['code'] = strtoupper($validated['code']);
        }

        $lab->update($validated);

        Log::info('Lab updated', [
            'lab_id' => $lab->id,
            'name' => $lab->name,
            'updated_by' => $user->id,
            'role' => $user->role,
        ]);

        return redirect()->route('admin.labs.index')
            ->with('success', '✅ Laboratorium <strong>' . e($lab->name) . '</strong> berhasil diupdate!');
    }

    /**
     * Delete lab (Admin only)
     */
    public function destroy(Lab $lab)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat menghapus laboratorium.');
        }

        $confirmedBookings = $lab->bookings()->where('status', 'confirmed')->count();
        if ($confirmedBookings > 0) {
            return back()->with('error', "❌ Laboratorium tidak dapat dihapus karena masih ada {$confirmedBookings} booking yang dikonfirmasi!");
        }

        $classSchedules = $lab->classSchedules()->where('status', 'active')->count();
        if ($classSchedules > 0) {
            return back()->with('error', "❌ Laboratorium tidak dapat dihapus karena masih ada {$classSchedules} jadwal kuliah aktif!");
        }

        Log::info('Lab deleted', [
            'lab_id' => $lab->id,
            'lab_name' => $lab->name,
            'deleted_by' => Auth::id(),
        ]);

        $lab->delete();

        return redirect()->route('admin.labs.index')
            ->with('success', '✅ Laboratorium <strong>' . e($lab->name) . '</strong> berhasil dihapus!');
    }

    /**
     * ✅ AJAX: Get available labs for dropdown
     */
    public function getAvailableLabs(Request $request)
    {
        $user = Auth::user();
        $query = Lab::where('status', 'active');

        if ($user->role === 'kalab' && !empty($user->lab_name)) {
            $query->where('name', $user->lab_name);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }

        $labs = $query->orderBy('name')->get(['id', 'name', 'code', 'location', 'capacity']);
        return response()->json($labs);
    }

    /**
     * ✅ AJAX: Get lab stats for modal/detail (FIXED)
     */
    public function getLabStats(Lab $lab)
    {
        $user = Auth::user();

        if ($user->role === 'kalab' && !empty($user->lab_name) && $lab->name !== $user->lab_name) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_bookings' => Booking::where('lab_name', $lab->name)->count(),
            'confirmed_bookings' => Booking::where('lab_name', $lab->name)
                ->where('status', 'confirmed')
                ->count(),
            'pending_bookings' => Booking::where('lab_name', $lab->name)
                ->whereIn('status', ['pending', 'approved_dosen', 'approved_teknisi'])
                ->count(),
            'active_schedules' => ClassSchedule::where('lab_name', $lab->name)
                ->where('status', 'active')
                ->count(),
            'today_bookings' => Booking::where('lab_name', $lab->name)
                ->whereDate('booking_date', today())
                ->count(),
        ];

        return response()->json($stats);
    }
}
