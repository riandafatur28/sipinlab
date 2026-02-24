<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    /**
     * Display dashboard admin
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'mahasiswa' => User::where('role', 'mahasiswa')->count(),
            'dosen' => User::where('role', 'dosen')->count(),
            'ketua_lab' => User::where('role', 'ketua_lab')->count(),
            'teknisi' => User::where('role', 'teknisi')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Display list of users
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show form to create new user
     */
    public function createUser()
    {
        $roles = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'ketua_lab' => 'Ketua Lab',
            'teknisi' => 'Teknisi',
            'admin' => 'Admin',
        ];

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:mahasiswa,dosen,ketua_lab,teknisi,admin'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Validasi domain email berdasarkan role
        $domain = substr(strrchr($validated['email'], "@"), 1);

        if ($request->role === 'mahasiswa') {
            if ($domain !== 'student.polije.ac.id') {
                return back()->withErrors(['email' => 'Email mahasiswa harus menggunakan domain @student.polije.ac.id']);
            }
        } else {
            if ($domain !== 'polije.ac.id') {
                return back()->withErrors(['email' => 'Email staff harus menggunakan domain @polije.ac.id']);
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Show user detail
     */
    public function showUser(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show form to edit user
     */
    public function editUser(User $user)
    {
        $roles = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'ketua_lab' => 'Ketua Lab',
            'teknisi' => 'Teknisi',
            'admin' => 'Admin',
        ];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:mahasiswa,dosen,ketua_lab,teknisi,admin'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ];

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User berhasil diupdate!');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak bisa menghapus akun sendiri!']);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus!');
    }
}
