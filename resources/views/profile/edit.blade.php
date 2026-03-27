@extends('layouts.app')

@section('title', 'Edit Profil - SiPinLab')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('profile.show') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">✏️ Edit Profil</h1>
                <p class="text-gray-600 mt-1">Perbarui informasi pribadi Anda</p>
            </div>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('profile.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Avatar Display -->
            <div class="flex items-center gap-6 pb-6 border-b border-gray-200">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Foto Profil</p>
                    <p class="text-xs text-gray-500">Foto akan ditampilkan sebagai inisial nama</p>
                </div>
            </div>

            <!-- Form Fields Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Email tidak dapat diubah</p>
                </div>

                <!-- NIM / NIP (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $user->nim_nip_label }}
                    </label>
                    <div class="px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-700">
                        {{ $user->nim_nip ?? 'Belum diisi oleh Admin' }}
                    </div>
                    <p class="mt-1 text-xs text-gray-400">
                        🔒 Data ini dikelola oleh Admin dan tidak dapat diubah
                    </p>
                </div>

                <!-- Program Studi (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                    <input type="text" value="{{ $user->prodi ?? '-' }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Program studi tidak dapat diubah</p>
                </div>

                <!-- No. Telepon -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        No. Telepon / WhatsApp
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-3 py-2 border @error('phone') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                           placeholder="08xxxxxxxxxx">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <input type="text" value="{{ ucfirst($user->role) }}" disabled
                           class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed capitalize">
                </div>

                <!-- Status Akun (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                    <div class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg">
                        <span class="{{ $user->account_status['class'] }} font-medium">
                            {{ $user->account_status['dot'] }} {{ $user->account_status['label'] }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $user->account_status['label'] === 'Aktif' ? '✓ Akun dapat digunakan untuk login' : '⚠️ Akun dinonaktifkan oleh Admin' }}
                    </p>
                </div>

                <!-- Status Kalab (HANYA DOSEN/KALAB/ADMIN) -->
                @if($user->canSeeKalabStatus())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Kalab</label>
                    <div class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg">
                        @if($user->kalab_status)
                            <span class="{{ $user->kalab_status['class'] }} font-medium">
                                {{ $user->kalab_status['label'] }}
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ $user->is_kalab ? '✓ Memiliki hak akses manajemen laboratorium' : 'ℹ️ Hubungi Admin untuk penunjukan sebagai Kalab' }}
                    </p>
                </div>
                @endif

            </div>

            <!-- Bio / Deskripsi -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">
                    Bio / Deskripsi Singkat
                </label>
                <textarea name="bio" id="bio" rows="3"
                          class="w-full px-3 py-2 border @error('bio') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                          placeholder="Tuliskan deskripsi singkat tentang Anda...">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('profile.show') }}"
                   class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    ← Batal
                </a>
                <div class="flex items-center gap-3">
                    <button type="reset"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors text-sm font-medium">
                        Reset
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
