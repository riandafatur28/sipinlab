@extends('layouts.app')

@section('title', 'Edit Laboratorium - Admin')

@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.labs.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center gap-2 text-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar Lab
        </a>
        <h1 class="text-3xl font-bold text-gray-800">✏️ Edit Laboratorium</h1>
        <p class="text-gray-600">Update data laboratorium</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {!! session('success') !!}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg text-red-700">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Edit Form -->
    <form action="{{ route('admin.labs.update', $lab) }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf
        @method('PUT')

        <!-- Lab Info Card -->
        <div class="mb-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-200">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    🏢
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800">{{ $lab->name }}</h3>
                    <p class="text-gray-600 text-sm">Kode: <strong>{{ $lab->code }}</strong></p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-block px-3 py-1 text-xs rounded-full font-semibold
                            @if($lab->status === 'active') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $lab->status === 'active' ? '✅ Aktif' : '⏸️ Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nama Laboratorium -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Laboratorium <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $lab->name) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                   placeholder="Contoh: Multimedia Cerdas (MMC)">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Kode Lab -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Lab <span class="text-red-500">*</span></label>
            <input type="text" name="code" value="{{ old('code', $lab->code) }}" required maxlength="10"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('code') border-red-500 @enderror uppercase"
                   placeholder="Contoh: MMC" style="text-transform: uppercase;">
            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Maksimal 10 karakter, akan diubah menjadi huruf besar otomatis</p>
        </div>

        <!-- Deskripsi -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                      placeholder="Jelaskan fasilitas dan kegunaan laboratorium...">{{ old('description', $lab->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Maksimal 1000 karakter</p>
        </div>

        <!-- Lokasi -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi / Gedung</label>
            <input type="text" name="location" value="{{ old('location', $lab->location) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('location') border-red-500 @enderror"
                   placeholder="Contoh: Gedung A, Lantai 3">
            @error('location') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Kapasitas -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas (Jumlah Orang) <span class="text-red-500">*</span></label>
            <input type="number" name="capacity" value="{{ old('capacity', $lab->capacity) }}" required min="1"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('capacity') border-red-500 @enderror"
                   placeholder="Contoh: 30">
            @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
            <select name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror">
                <option value="">-- Pilih Status --</option>
                <option value="active" {{ old('status', $lab->status) === 'active' ? 'selected' : '' }}>✅ Aktif</option>
                <option value="inactive" {{ old('status', $lab->status) === 'inactive' ? 'selected' : '' }}>⏸️ Nonaktif</option>
            </select>
            @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Lab nonaktif tidak akan muncul dalam pilihan booking</p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex flex-wrap gap-3">

                <!-- Delete Lab Button -->
                <button type="button" onclick="openModal('deleteModal')"
                        class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus Lab
                </button>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.labs.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">Batal</a>
            <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors shadow-lg">✅ Update Lab</button>
        </div>
    </form>
</div>

<!-- ======================================================================== -->
<!-- ✅ MODAL: Delete Lab -->
<!-- ======================================================================== -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal('deleteModal')"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl w-full max-w-md border border-gray-200">
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">🗑️ Hapus Laboratorium</h3>
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
                            <p class="text-gray-700 font-medium">Hapus laboratorium ini secara permanen?</p>
                            <p class="text-sm text-gray-500 mt-1">Tindakan ini <strong class="text-red-600">tidak dapat dibatalkan</strong>.</p>
                        </div>
                    </div>
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-gray-700"><strong>{{ $lab->name }}</strong></p>
                        <p class="text-xs text-gray-500 mt-1">Kode: {{ $lab->code }} • Kapasitas: {{ $lab->capacity }} orang</p>
                    </div>
                    @php
                        $bookingCount = \App\Models\Booking::where('lab_name', $lab->name)->count();
                    @endphp
                    @if($bookingCount > 0)
                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs text-yellow-700">⚠️ Laboratorium ini memiliki <strong>{{ $bookingCount }} booking</strong>. Data booking akan tetap tersimpan.</p>
                    </div>
                    @endif
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">Batal</button>
                    <form action="{{ route('admin.labs.destroy', $lab) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">🗑️ Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================== -->
{{-- <!-- ✅ INLINE SCRIPTS (TANPA @push/@endpush - Langsung di dalam @section) --> --}}
<!-- ======================================================================== -->
<script>
(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('🎭 Modal script initialized for lab edit');

        // Open modal function
        window.openModal = function(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        };

        // Close modal function
        window.closeModal = function(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        };

        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var modals = document.querySelectorAll('[id$="Modal"]:not(.hidden)');
                for (var i = 0; i < modals.length; i++) {
                    closeModal(modals[i].id);
                }
            }
        });

        // Close when clicking backdrop
        var backdrops = document.querySelectorAll('[id$="Modal"] > div:first-child');
        for (var i = 0; i < backdrops.length; i++) {
            backdrops[i].addEventListener('click', function() {
                closeModal(this.parentElement.id);
            });
        }

        // Prevent modal panel click from closing
        var panels = document.querySelectorAll('[id$="Modal"] .bg-white');
        for (var i = 0; i < panels.length; i++) {
            panels[i].addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Auto-uppercase code input
        var codeInput = document.querySelector('input[name="code"]');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    }
})();
</script>

@endsection
