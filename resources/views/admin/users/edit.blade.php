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
        <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800">{{ $user->name }}</h3>
                    <p class="text-gray-600 text-sm">{{ $user->email }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-block px-3 py-1 text-xs rounded-full font-semibold
                            @if($user->role === 'mahasiswa') bg-blue-100 text-blue-800
                            @elseif($user->role === 'dosen') bg-green-100 text-green-800
                            @elseif($user->role === 'teknisi') bg-yellow-100 text-yellow-800
                            @elseif($user->role === 'ketua_lab') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                        @if($user->lab_name)
                            <span class="inline-block px-3 py-1 text-xs rounded-full font-semibold bg-indigo-100 text-indigo-800">
                                🔧 {{ $user->lab_name }}
                            </span>
                        @endif
                        @if($user->is_kalab)
                            <span class="badge-kalab">👔 Kalab</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Nama Lengkap -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Email -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- NIM/NIP (Read-only) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $user->isMahasiswa() ? 'NIM' : 'NIP' }}</label>
            <input type="text" value="{{ $user->isMahasiswa() ? $user->nim : $user->nip }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600" readonly>
            <p class="mt-1 text-xs text-gray-500">Tidak dapat diubah</p>
        </div>

        <!-- Role (Read-only) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Role / Jabatan</label>
            <input type="text" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600" readonly>
            <p class="mt-1 text-sm text-gray-500">Role tidak dapat diubah. Hapus dan buat user baru jika perlu mengubah role.</p>
        </div>

        <!-- Phone -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon / WhatsApp</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex flex-wrap gap-3">

                <!-- Reset Password Button -->
                <button type="button" onclick="openModal('resetPasswordModal')"
                        class="flex items-center gap-2 px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm font-medium hover:bg-orange-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset Password
                </button>

                <!-- Delete User Button -->
                @if($user->role !== 'admin')
                <button type="button" onclick="openModal('deleteModal')"
                        class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus User
                </button>
                @endif
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">Batal</a>
            <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-lg">✅ Update User</button>
        </div>
    </form>
</div>

<!-- ✅ MODAL: Reset Password -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal('resetPasswordModal')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl w-full max-w-md border border-gray-200">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">🔄 Reset Password</h3>
                        <button type="button" onclick="closeModal('resetPasswordModal')" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-700 font-medium">Reset password user ini?</p>
                            <p class="text-sm text-gray-500 mt-1">Password akan direset ke: <strong class="text-orange-600">{{ $user->nim ?? $user->nip ?? explode('@', $user->email)[0] }}</strong></p>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600"><strong>{{ $user->name }}</strong> <span class="text-gray-400">({{ $user->email }})</span></p>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('resetPasswordModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Batal</button>
                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg text-sm font-medium hover:bg-orange-700">✅ Ya, Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ MODAL: Delete User -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal('deleteModal')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl w-full max-w-md border border-gray-200">
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">🗑️ Hapus User</h3>
                        <button type="button" onclick="closeModal('deleteModal')" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-gray-700 font-medium">Hapus user ini secara permanen?</p>
                            <p class="text-sm text-gray-500 mt-1">Tindakan ini <strong class="text-red-600">tidak dapat dibatalkan</strong>.</p>
                        </div>
                    </div>
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-gray-700"><strong>{{ $user->name }}</strong></p>
                        <p class="text-xs text-gray-500 mt-1">{{ $user->email }} • {{ ucfirst(str_replace('_', ' ', $user->role)) }} @if($user->lab_name) • {{ $user->lab_name }} @endif</p>
                    </div>
                    @if($user->isKalab())
                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs text-yellow-700">⚠️ User ini adalah <strong>Kalab</strong>. Jabatan Kalab akan otomatis dicopot.</p>
                    </div>
                    @endif
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Batal</button>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">🗑️ Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ✅ Modal Functions - Inline fallback jika @stack tidak bekerja
(function() {
    // Debug: Cek apakah script loaded
    console.log('🎭 Modal script loaded');

    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            console.log('🔓 Opening modal:', modalId);
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const firstBtn = modal.querySelector('button');
                if (firstBtn) firstBtn.focus();
            }, 100);
        } else {
            console.error('❌ Modal not found:', modalId);
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            console.log('🔒 Closing modal:', modalId);
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    // Close with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id$="Modal"]:not(.hidden)').forEach(m => closeModal(m.id));
        }
    });

    // Close when clicking backdrop
    document.querySelectorAll('[id$="Modal"] > div:first-child').forEach(backdrop => {
        backdrop.addEventListener('click', function() {
            closeModal(this.parentElement.id);
        });
    });

    // Prevent modal panel click from closing
    document.querySelectorAll('[id$="Modal"] .bg-white').forEach(panel => {
        panel.addEventListener('click', e => e.stopPropagation());
    });
})();
</script>
@endpush
