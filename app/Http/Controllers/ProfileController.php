<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information and password.
     *
     * ✅ FIX:
     * 1. Gunakan array merge yang aman untuk nullable fields
     * 2. Pastikan password hanya di-hash jika tidak kosong
     * 3. Tambahkan error handling untuk validasi current_password
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // ✅ Validasi input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'telegram_action' => ['nullable', 'string', 'in:unlink'],
            'telegram_chat_id' => ['nullable', 'string', 'max:30', 'regex:/^\d+$/'],

            // Password validation (hanya jika user ingin mengubah)
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.required_with' => 'Password saat ini wajib diisi untuk mengubah password.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'telegram_chat_id.regex' => 'Chat ID hanya boleh berisi angka.',
        ]);

        // ✅ Siapkan data untuk update (field yang boleh diedit user)
        $updateData = [
            'name' => $validated['name'],
        ];

        // Handle nullable fields dengan aman
        if (isset($validated['phone'])) {
            $updateData['phone'] = $validated['phone'];
        }
        if (isset($validated['bio'])) {
            $updateData['bio'] = $validated['bio'];
        }

        // Handle Telegram Chat ID
        if (($validated['telegram_action'] ?? '') === 'unlink') {
            $updateData['telegram_chat_id'] = null;
        } elseif (!empty($validated['telegram_chat_id'])) {
            $updateData['telegram_chat_id'] = $validated['telegram_chat_id'];
        }

        // ✅ Update profil
        $user->update($updateData);

        // ✅ Update password hanya jika diisi dan valid
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        if (($validated['telegram_action'] ?? '') === 'unlink') {
            $successMsg = '✅ Akun Telegram berhasil diputuskan.';
        } elseif (!empty($validated['telegram_chat_id'])) {
            $successMsg = '✅ Telegram Chat ID berhasil disimpan! Notifikasi Telegram sekarang aktif.';
        } else {
            $successMsg = '✅ Profil berhasil diperbarui!';
        }

        // ✅ Redirect dengan success message
        return redirect()->route('profile.show')
            ->with('success', $successMsg);
    }

    /**
     * Dedicated method untuk update password saja (opsional, untuk modal)
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', '🔐 Password berhasil diubah!');
    }
}
