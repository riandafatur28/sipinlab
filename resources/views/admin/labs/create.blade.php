@extends('layouts.app')

@section('title', 'Tambah Laboratorium - Admin')

@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Laboratorium Baru</h1>
        <p class="text-gray-600">Input data laboratorium untuk sistem booking</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg">
        <ul class="list-disc list-inside text-red-700 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.labs.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf

        <!-- Nama Laboratorium -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nama Laboratorium <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" required maxlength="255"
                   value="{{ old('name') }}"
                   placeholder="Contoh: Multimedia Cerdas (MMC)"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kode Lab -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Kode Singkat <span class="text-red-500">*</span>
            </label>
            <input type="text" name="code" required maxlength="10"
                   value="{{ old('code') }}"
                   placeholder="Contoh: MMC"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('code') border-red-500 @enderror"
                   oninput="this.value = this.value.toUpperCase()">
            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-sm text-gray-500">Kode unik untuk identifikasi lab (maks 10 karakter)</p>
        </div>

        <!-- Deskripsi -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Deskripsi
            </label>
            <textarea name="description" rows="3" maxlength="1000"
                      placeholder="Jelaskan fasilitas dan kegunaan laboratorium..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-400">Maksimal 1000 karakter</p>
        </div>

        <!-- Lokasi -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Lokasi
            </label>
            <input type="text" name="location" maxlength="255"
                   value="{{ old('location') }}"
                   placeholder="Contoh: Gedung A, Lantai 3, Ruang 301"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location') border-red-500 @enderror">
            @error('location')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kapasitas -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Kapasitas <span class="text-red-500">*</span>
            </label>
            <input type="number" name="capacity" required min="1" max="200"
                   value="{{ old('capacity', 30) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('capacity') border-red-500 @enderror">
            @error('capacity')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-sm text-gray-500">Jumlah maksimal pengguna yang dapat ditampung</p>
        </div>

        <!-- Status -->
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Status <span class="text-red-500">*</span>
            </label>
            <select name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>✓ Aktif - Dapat dibooking</option>
                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>✗ Non-Aktif - Tidak dapat dibooking</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.labs.index') }}"
               class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Laboratorium
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
// Auto-focus first input
document.querySelector('input[name="name"]')?.focus();

// Real-time character counter for description
const descInput = document.querySelector('textarea[name="description"]');
if (descInput) {
    descInput.addEventListener('input', function() {
        const count = this.value.length;
        const max = this.maxLength;
        // You can add a counter display here if needed
    });
}

// Prevent form submission on Enter key in textarea
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            // Allow Ctrl+Enter to submit if needed
        }
    });
});
</script>
@endpush

@endsection