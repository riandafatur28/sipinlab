@extends('layouts.app')

@section('title', 'Edit User - Admin')

@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Edit User</h1>
        <p class="text-gray-600">Update data pengguna</p>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf
        @method('PUT')

        <!-- User Info Card -->
        <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl font-bold">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $user->name }}</h3>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full font-semibold
                        @if($user->role === 'mahasiswa') bg-blue-100 text-blue-800
                        @elseif($user->role === 'dosen') bg-green-100 text-green-800
                        @elseif($user->role === 'teknisi') bg-yellow-100 text-yellow-800
                        @elseif($user->role === 'ketua_lab') bg-purple-100 text-purple-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Nama Lengkap -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Email -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Role (Read-only) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Role / Jabatan
            </label>
            <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-600" readonly>
            <p class="mt-2 text-sm text-gray-500">Role tidak dapat diubah. Hapus dan buat user baru jika perlu mengubah role.</p>
        </div>

        <!-- Reset Password Button -->
        <div class="mb-8 p-4 bg-orange-50 border border-orange-300 rounded-lg">
            <h4 class="font-semibold text-orange-800 mb-2">🔄 Reset Password</h4>
            <p class="text-sm text-orange-700 mb-3">
                Reset password user ini ke NIM/NIP default?
            </p>
            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Reset password ke {{ explode('@', $user->email)[0] }}?')"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg text-sm hover:bg-orange-700 transition-colors">
                    Reset Password
                </button>
            </form>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.users.index') }}"
               class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-lg">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection
