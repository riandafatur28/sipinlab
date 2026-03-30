<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Lab;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassScheduleController extends Controller
{
    /**
     * Display class schedules
     */
    public function index(Request $request)
    {
        $query = ClassSchedule::with('lecturer');

        // Filter queries
        if ($request->filled('lab')) {
            $query->where('lab_name', $request->lab);
        }
        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }
        if ($request->filled('golongan')) {
            $query->where('golongan', $request->golongan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('course_name', 'like', "%{$request->search}%")
                  ->orWhere('course_code', 'like', "%{$request->search}%");
            });
        }

        // ✅ FIX: Urutan Tabel & Pagination 10 baris
        $schedules = $query->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')")
                          ->orderBy('start_time', 'asc')
                          ->paginate(10)
                          ->withQueryString();

        $labs = Lab::where('status', 'active')->pluck('name', 'name');
        $lecturers = User::where('role', 'dosen')->pluck('name', 'id');
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $golongans = ['A', 'B', 'C'];

        // ✅ FIX: Hitung Total Mahasiswa Unik (Anti Duplikasi)
        $uniqueStudentStats = ClassSchedule::select('semester', 'golongan', 'students_count')
            ->where('status', 'active')
            ->when($request->filled('lab'), fn($q) => $q->where('lab_name', $request->lab))
            ->distinct()
            ->get()
            ->groupBy(function($item) {
                return "{$item->semester}-{$item->golongan}";
            })
            ->map(fn($group) => $group->first()->students_count)
            ->sum();

        // ✅ FIX: Hitung jadwal hari ini - gunakan FIELD() untuk match nama hari
        $todayName = Carbon::now()->locale('id')->dayName; // 'Senin', 'Selasa', dst
        $hariIniCount = ClassSchedule::whereRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat') = FIELD(?, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')", [$todayName])
            ->where('status', 'active')
            ->count();

        $stats = [
            'total_jadwal' => $schedules->total(),
            'total_mahasiswa_unik' => $uniqueStudentStats,
            'total_golongan_aktif' => ClassSchedule::where('status', 'active')
                ->distinct('golongan')
                ->count('golongan'),
            'lab_terpakai' => $schedules->pluck('lab_name')->unique()->count(),
            'hari_ini' => $hariIniCount, // ✅ Sekarang akan menampilkan angka yang benar
        ];

        // ✅ Return partial view if AJAX request (for live search & filters)
        if ($request->ajax()) {
            return view('admin.class-schedules.partials.table', compact('schedules'))->render();
        }

        return view('admin.class-schedules.index', compact('schedules', 'labs', 'lecturers', 'days', 'golongans', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $labs = Lab::where('status', 'active')->pluck('name', 'name');
        $lecturers = User::where('role', 'dosen')->pluck('name', 'id');
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $golongans = ['A', 'B', 'C'];

        $sessions = [
            'Sesi 1 (07:00 - 08:00)', 'Sesi 2 (08:00 - 09:00)', 'Sesi 3 (09:00 - 10:00)',
            'Sesi 4 (10:00 - 11:00)', 'Sesi 5 (13:00 - 14:00)', 'Sesi 6 (14:00 - 15:00)',
            'Sesi 7 (15:00 - 16:00)', 'Sesi 8 (16:00 - 17:00)',
        ];

        return view('admin.class-schedules.create', compact('labs', 'lecturers', 'days', 'golongans', 'sessions'));
    }

    /**
     * Store new class schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_name' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:20',
            'class_name' => 'required|string|max:20',
            'golongan' => ['required', Rule::in(['A', 'B', 'C'])],
            'lecturer_id' => 'required|exists:users,id',
            'semester' => 'required|integer|min:1|max:14',
            'day' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'students_count' => 'required|integer|min:1|max:200',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:500',
        ], [
            'golongan.in' => 'Golongan harus A, B, atau C',
            'day.in' => 'Hari hanya Senin sampai Jumat',
            'end_time.after' => 'Jam selesai harus setelah jam mulai',
        ]);

        // ✅ VALIDASI 1: Cek Konflik Jadwal (Time Overlap)
        $conflict = ClassSchedule::where('lab_name', $validated['lab_name'])
            ->where('day', $validated['day'])
            ->where('golongan', $validated['golongan'])
            ->where('status', 'active')
            ->where(function($q) use ($validated) {
                $q->where(function($sub) use ($validated) {
                    $sub->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhere(function($sub2) use ($validated) {
                            $sub2->where('start_time', '<=', $validated['start_time'])
                                 ->where('end_time', '>=', $validated['end_time']);
                        });
                });
            })
            ->first();

        if ($conflict) {
            return back()->withErrors([
                'end_time' => '⚠️ Jadwal bentrok! Sudah ada jadwal pada rentang waktu yang sama.'
            ])->withInput();
        }

        // ✅ VALIDASI 2: Cek Konsistensi students_count
        $existingGroup = ClassSchedule::where('semester', $validated['semester'])
            ->where('golongan', $validated['golongan'])
            ->where('status', 'active')
            ->first();

        if ($existingGroup && $existingGroup->students_count !== $validated['students_count']) {
            return back()->withErrors([
                'students_count' => "⚠️ Data tidak konsisten! Golongan {$validated['golongan']} Semester {$validated['semester']} sudah terdaftar dengan {$existingGroup->students_count} mahasiswa. Harap gunakan angka yang sama."
            ])->withInput();
        }

        ClassSchedule::create($validated);

        return redirect()->route('admin.class-schedules.index')
            ->with('success', '✅ Jadwal kuliah berhasil ditambahkan!');
    }

    /**
     * Show class schedule detail
     */
    public function show(ClassSchedule $classSchedule)
    {
        return view('admin.class-schedules.show', compact('classSchedule'));
    }

    /**
     * Show edit form
     */
    public function edit(ClassSchedule $classSchedule)
    {
        $labs = Lab::where('status', 'active')->pluck('name', 'name');
        $lecturers = User::where('role', 'dosen')->pluck('name', 'id');
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $golongans = ['A', 'B', 'C'];

        $sessions = [
            'Sesi 1 (07:00 - 08:00)', 'Sesi 2 (08:00 - 09:00)', 'Sesi 3 (09:00 - 10:00)',
            'Sesi 4 (10:00 - 11:00)', 'Sesi 5 (13:00 - 14:00)', 'Sesi 6 (14:00 - 15:00)',
            'Sesi 7 (15:00 - 16:00)', 'Sesi 8 (16:00 - 17:00)',
        ];

        return view('admin.class-schedules.edit', compact('classSchedule', 'labs', 'lecturers', 'days', 'golongans', 'sessions'));
    }

    /**
     * Update class schedule
     */
    public function update(Request $request, ClassSchedule $classSchedule)
    {
        $validated = $request->validate([
            'lab_name' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:20',
            'class_name' => 'required|string|max:20',
            'golongan' => ['required', Rule::in(['A', 'B', 'C'])],
            'lecturer_id' => 'required|exists:users,id',
            'semester' => 'required|integer|min:1|max:14',
            'day' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'students_count' => 'required|integer|min:1|max:200',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:500',
        ], [
            'golongan.in' => 'Golongan harus A, B, atau C',
            'day.in' => 'Hari hanya Senin sampai Jumat',
            'end_time.after' => 'Jam selesai harus setelah jam mulai',
        ]);

        // ✅ VALIDASI 1: Cek Konflik Jadwal (Exclude current record)
        $conflict = ClassSchedule::where('lab_name', $validated['lab_name'])
            ->where('day', $validated['day'])
            ->where('golongan', $validated['golongan'])
            ->where('status', 'active')
            ->where('id', '!=', $classSchedule->id)
            ->where(function($q) use ($validated) {
                $q->where(function($sub) use ($validated) {
                    $sub->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhere(function($sub2) use ($validated) {
                            $sub2->where('start_time', '<=', $validated['start_time'])
                                 ->where('end_time', '>=', $validated['end_time']);
                        });
                });
            })
            ->first();

        if ($conflict) {
            return back()->withErrors([
                'end_time' => '⚠️ Jadwal bentrok! Sudah ada jadwal pada rentang waktu yang sama.'
            ])->withInput();
        }

        // ✅ VALIDASI 2: Cek Konsistensi students_count
        $existingGroup = ClassSchedule::where('semester', $validated['semester'])
            ->where('golongan', $validated['golongan'])
            ->where('status', 'active')
            ->where('id', '!=', $classSchedule->id)
            ->first();

        if ($existingGroup && $existingGroup->students_count !== $validated['students_count']) {
            return back()->withErrors([
                'students_count' => "⚠️ Data tidak konsisten! Golongan {$validated['golongan']} Semester {$validated['semester']} sudah terdaftar dengan {$existingGroup->students_count} mahasiswa."
            ])->withInput();
        }

        $classSchedule->update($validated);

        return redirect()->route('admin.class-schedules.index')
            ->with('success', '✅ Jadwal kuliah berhasil diupdate!');
    }

    /**
     * Delete class schedule
     */
    public function destroy(ClassSchedule $classSchedule)
    {
        $classSchedule->delete();
        return redirect()->route('admin.class-schedules.index')
            ->with('success', '🗑️ Jadwal kuliah berhasil dihapus!');
    }
}
