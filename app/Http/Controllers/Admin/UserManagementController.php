<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        // Search by name or email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
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
            'nim_nip' => ['required', 'string', 'max:20', 'unique:users,email'],
        ]);

        // Default password = NIM/NIP
        $password = $validated['nim_nip'];

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "✅ User berhasil dibuat! Password default: {$password}");
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
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', '✅ User berhasil diupdate!');
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', '❌ Tidak dapat menghapus admin!');
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
        // Extract NIM/NIP from email
        $defaultPassword = explode('@', $user->email)[0];

        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "✅ Password direset ke: {$defaultPassword}");
    }
}
