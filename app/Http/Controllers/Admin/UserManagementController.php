<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class UserManagementController extends Controller
{
    /**
     * Display list of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter: Only show Kalab
        if ($request->filled('only_kalab') && $request->only_kalab == '1') {
            $query->where('role', 'dosen')->where('is_kalab', true);
        }

        // Search by name, email, NIM, or NIP
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('nim', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:mahasiswa,dosen,teknisi,ketua_lab'],
            'nim_nip' => ['required', 'string', 'max:20'],
            'nim' => ['nullable', 'string', 'max:20', 'unique:users,nim'],
            'nip' => ['nullable', 'string', 'max:20', 'unique:users,nip'],
            'prodi' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_kalab' => ['nullable', 'boolean'], // ✅ Validasi is_kalab
        ]);

        // Default password = NIM/NIP
        $password = $validated['nim_nip'];

        // ✅ Logic: Hanya dosen yang bisa jadi kalab
        if (!empty($validated['is_kalab']) && $validated['role'] !== 'dosen') {
            return back()->withInput()->with('error', '❌ Hanya user dengan role <strong>Dosen</strong> yang dapat diangkat menjadi Kalab!');
        }

        // ✅ Logic: Jika diangkat jadi kalab, copot kalab sebelumnya
        if (!empty($validated['is_kalab'])) {
            User::where('role', 'dosen')->where('is_kalab', true)->update(['is_kalab' => false]);
            Log::info('Kalab position transferred', [
                'new_kalab' => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        // Siapkan data untuk disimpan
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'email_verified_at' => now(),
            'prodi' => $validated['prodi'] ?? null,
            'golongan' => $validated['golongan'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_kalab' => $validated['is_kalab'] ?? false,
        ];

        // Set NIM/NIP sesuai role
        if ($validated['role'] === 'mahasiswa') {
            $userData['nim'] = $validated['nim'] ?? null;
        } elseif ($validated['role'] === 'dosen') {
            $userData['nip'] = $validated['nip'] ?? null;
        }

        User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', "✅ User berhasil dibuat! Password default: <strong>{$password}</strong>");
    }

    /**
     * Show user detail
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show edit form
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:mahasiswa,dosen,teknisi,ketua_lab'],
            'nim' => ['nullable', 'string', 'max:20', 'unique:users,nim,' . $user->id],
            'nip' => ['nullable', 'string', 'max:20', 'unique:users,nip,' . $user->id],
            'prodi' => ['nullable', 'string', 'max:255'],
            'golongan' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_kalab' => ['nullable', 'boolean'], // ✅ Validasi is_kalab
        ]);

        // ✅ Logic: Hanya dosen yang bisa jadi kalab
        if (!empty($validated['is_kalab']) && $validated['role'] !== 'dosen') {
            return back()->withInput()->with('error', '❌ Hanya user dengan role <strong>Dosen</strong> yang dapat diangkat menjadi Kalab!');
        }

        // ✅ Logic: Jika user ini diangkat jadi kalab, copot yang lama
        if (!empty($validated['is_kalab']) && !$user->is_kalab) {
            User::where('role', 'dosen')
                ->where('is_kalab', true)
                ->where('id', '!=', $user->id)
                ->update(['is_kalab' => false]);

            Log::info('Kalab position transferred', [
                'new_kalab' => $user->name,
                'previous_kalab' => User::where('is_kalab', true)->where('id', '!=', $user->id)->first()?->name,
            ]);
        }

        // ✅ Jika role diubah dari dosen ke non-dosen, otomatis copot jabatan kalab
        if ($validated['role'] !== 'dosen' && $user->is_kalab) {
            $validated['is_kalab'] = false;
        }

        // Siapkan data update
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'prodi' => $validated['prodi'] ?? null,
            'golongan' => $validated['golongan'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_kalab' => $validated['is_kalab'] ?? false,
        ];

        // Set NIM/NIP sesuai role
        if ($validated['role'] === 'mahasiswa') {
            $updateData['nim'] = $validated['nim'] ?? null;
            $updateData['nip'] = null; // Clear NIP jika bukan dosen
        } elseif ($validated['role'] === 'dosen') {
            $updateData['nip'] = $validated['nip'] ?? null;
            $updateData['nim'] = null; // Clear NIM jika bukan mahasiswa
        } else {
            $updateData['nim'] = null;
            $updateData['nip'] = null;
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', '✅ User berhasil diupdate!');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // ✅ Tidak bisa hapus admin
        if ($user->role === 'admin') {
            return back()->with('error', '❌ Tidak dapat menghapus admin!');
        }

        // ✅ Jika user yang dihapus adalah Kalab aktif, beri warning
        if ($user->isKalab()) {
            Log::warning('Kalab user deleted', [
                'kalab_name' => $user->name,
                'deleted_by' => auth()->user()?->name,
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', '✅ User berhasil dihapus!');
    }

    /**
     * Reset user password to default (NIM/NIP)
     */
    public function resetPassword(User $user)
    {
        // Extract NIM/NIP from email atau gunakan field yang ada
        $defaultPassword = $user->nim ?? $user->nip ?? explode('@', $user->email)[0];

        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        Log::info('User password reset', [
            'user_id' => $user->id,
            'email' => $user->email,
            'reset_by' => auth()->user()?->name,
        ]);

        return back()->with('success', "✅ Password direset ke: <strong>{$defaultPassword}</strong>");
    }

    /**
     * ✅ NEW: Transfer Kalab position to another dosen
     */
    public function transferKalab(Request $request)
    {
        $validated = $request->validate([
            'new_kalab_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $newKalab = User::find($validated['new_kalab_id']);

        // Validasi: Harus dosen dan bukan Kalab saat ini
        if (!$newKalab || !$newKalab->isDosen()) {
            return back()->with('error', '❌ User yang dipilih bukan Dosen!');
        }

        if ($newKalab->is_kalab) {
            return back()->with('info', 'ℹ️ User ini sudah menjabat sebagai Kalab.');
        }

        // ✅ Transfer jabatan: copot yang lama, angkat yang baru
        User::where('role', 'dosen')->where('is_kalab', true)->update(['is_kalab' => false]);
        $newKalab->update(['is_kalab' => true]);

        Log::info('Kalab transferred via admin panel', [
            'new_kalab' => $newKalab->name,
            'new_kalab_id' => $newKalab->id,
            'transferred_by' => auth()->user()?->name,
        ]);

        return back()->with('success', "✅ Jabatan Kalab berhasil dialihkan ke <strong>{$newKalab->name}</strong>!");
    }

    /**
     * ✅ NEW: Toggle is_kalab status (quick action)
     */
    public function toggleKalabStatus(User $user)
    {
        // Hanya bisa toggle untuk dosen
        if (!$user->isDosen()) {
            return back()->with('error', '❌ Hanya Dosen yang dapat memiliki jabatan Kalab!');
        }

        if ($user->is_kalab) {
            // Jika sudah Kalab, copot jabatan
            $user->update(['is_kalab' => false]);
            $message = "✅ Jabatan Kalab dicopot dari <strong>{$user->name}</strong>.";
        } else {
            // Jika belum Kalab, angkat jadi Kalab (copot yang lama otomatis)
            User::where('role', 'dosen')->where('is_kalab', true)->update(['is_kalab' => false]);
            $user->update(['is_kalab' => true]);
            $message = "✅ <strong>{$user->name}</strong> berhasil diangkat menjadi Kalab!";
        }

        Log::info('Kalab status toggled', [
            'user' => $user->name,
            'new_status' => $user->is_kalab,
            'by' => auth()->user()?->name,
        ]);

        return back()->with('success', $message);
    }
}
