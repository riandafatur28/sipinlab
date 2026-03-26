@extends('layouts.app')

@section('title', 'Tambah User - Admin')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah User Baru</h1>
        <p class="text-gray-600">Buat akun untuk mahasiswa, dosen, teknisi, atau ketua lab</p>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
            {!! session('success') !!}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
            {!! session('error') !!}
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf

        <!-- Role Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Role / Jabatan <span class="text-red-500">*</span>
            </label>
            <select name="role" id="role" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror"
                    onchange="toggleRoleFields()">
                <option value="">-- Pilih Role --</option>
                <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>🎓 Mahasiswa</option>
                <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>👨‍🏫 Dosen</option>
                <option value="teknisi" {{ old('role') == 'teknisi' ? 'selected' : '' }}>🔧 Teknisi</option>
                <option value="ketua_lab" {{ old('role') == 'ketua_lab' ? 'selected' : '' }}>👨‍💼 Ketua Laboratorium</option>
            </select>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Nama Lengkap -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" required
                   value="{{ old('name') }}"
                   placeholder="Contoh: Azka Riswanda"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- NIM / NIP -->
        <div class="mb-6" id="nimNipWrapper">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span id="nimLabel">NIM</span> <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nim_nip" id="nimNip" required
                   value="{{ old('nim_nip') }}"
                   placeholder="Contoh: e41231605"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nim_nip') border-red-500 @enderror"
                   oninput="generateEmail()">
            @error('nim_nip') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-sm text-gray-500">
                💡 <strong>Password default</strong> akan dibuat dari NIM/NIP ini
            </p>
        </div>

        <!-- Hidden fields for separate NIM/NIP validation -->
        <input type="hidden" name="nim" id="nimHidden" value="{{ old('nim') }}">
        <input type="hidden" name="nip" id="nipHidden" value="{{ old('nip') }}">

        <!-- Email (Auto-generated) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" id="email" required
                   value="{{ old('email') }}"
                   placeholder="nim@domain.polije.ac.id"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 @error('email') border-red-500 @enderror"
                   readonly>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-sm text-blue-600">
                📧 Email otomatis berdasarkan NIM/NIP
            </p>
        </div>

        <!-- Kalab Checkbox (Hanya untuk Dosen) -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg" id="kalabWrapper" style="display: none;">
            <div class="flex items-start">
                <input type="checkbox" name="is_kalab" id="is_kalab" value="1"
                       {{ old('is_kalab') ? 'checked' : '' }}
                       class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <div class="ml-3">
                    <label for="is_kalab" class="font-medium text-gray-700">
                        👔 Angkat sebagai Ketua Lab (Kalab)
                    </label>
                    <p class="text-sm text-gray-600 mt-1">
                        User ini akan memiliki hak akses approval level Kalab.
                        <span class="text-orange-600 font-medium">Jika dicentang, jabatan Kalab sebelumnya akan otomatis dicopot.</span>
                    </p>
                </div>
            </div>
            @error('is_kalab') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Program Studi (HANYA untuk Mahasiswa) -->
        <div class="mb-6" id="prodiWrapper" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Program Studi
            </label>
            <select name="prodi" id="prodi"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('prodi') border-red-500 @enderror">
                <option value="Teknik Informatika" selected>Teknik Informatika</option>
            </select>
            @error('prodi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Golongan (Khusus Mahasiswa) -->
        <div class="mb-6" id="golonganWrapper" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Golongan Praktikum
            </label>
            <select name="golongan"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('golongan') border-red-500 @enderror">
                <option value="">-- Pilih Golongan --</option>
                <option value="A" {{ old('golongan') == 'A' ? 'selected' : '' }}>Golongan A</option>
                <option value="B" {{ old('golongan') == 'B' ? 'selected' : '' }}>Golongan B</option>
                <option value="C" {{ old('golongan') == 'C' ? 'selected' : '' }}>Golongan C</option>
            </select>
            @error('golongan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Lab Assignment (KHUSUS Teknisi) -->
        <div class="mb-6" id="labWrapper" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Laboratorium <span class="text-red-500">*</span>
            </label>
            <select name="lab_name" id="lab_name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('lab_name') border-red-500 @enderror">
                <option value="">-- Pilih Laboratorium --</option>
                @php
                    $labs = \App\Models\Lab::where('status', 'active')->orderBy('name')->get();
                @endphp
                @foreach($labs as $lab)
                    <option value="{{ $lab->name }}" {{ old('lab_name') == $lab->name ? 'selected' : '' }}>
                        {{ $lab->name }}
                    </option>
                @endforeach
            </select>
            @error('lab_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-2 text-xs text-gray-500">
                💡 Teknisi akan ditugaskan khusus untuk laboratorium ini
            </p>
        </div>

        <!-- Phone / WhatsApp -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                No. Telepon / WhatsApp
            </label>
            <input type="text" name="phone"
                   value="{{ old('phone') }}"
                   placeholder="Contoh: 081234567890"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Password Preview -->
        <div class="mb-8 p-4 bg-yellow-50 border border-yellow-300 rounded-lg">
            <h4 class="font-semibold text-yellow-800 mb-2">🔐 Informasi Password</h4>
            <p class="text-sm text-yellow-700">
                Password default untuk user ini adalah: <strong id="passwordPreview">[NIM/NIP]</strong>
            </p>
            <p class="text-xs text-yellow-600 mt-2">
                User dapat mengganti password sendiri setelah login pertama kali.
            </p>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 pt-4 border-t">
            <a href="{{ route('admin.users.index') }}"
               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-lg">
                Buat Akun
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    toggleRoleFields();
});

function toggleRoleFields() {
    const role = document.getElementById('role').value;
    const nimLabel = document.getElementById('nimLabel');
    const nimInput = document.getElementById('nimNip');
    const nimNipWrapper = document.getElementById('nimNipWrapper');
    const kalabWrapper = document.getElementById('kalabWrapper');
    const prodiWrapper = document.getElementById('prodiWrapper');
    const golonganWrapper = document.getElementById('golonganWrapper');
    const labWrapper = document.getElementById('labWrapper');
    const nimHidden = document.getElementById('nimHidden');
    const nipHidden = document.getElementById('nipHidden');

    // Reset semua field dulu
    kalabWrapper.style.display = 'none';
    prodiWrapper.style.display = 'none';
    golonganWrapper.style.display = 'none';
    labWrapper.style.display = 'none';
    nimNipWrapper.style.display = 'block';
    nimInput.required = true;

    if (role === 'mahasiswa') {
        // Mahasiswa
        nimLabel.textContent = 'NIM';
        nimInput.placeholder = 'Contoh: e41231605';
        golonganWrapper.style.display = 'block';
        prodiWrapper.style.display = 'block';

        nimHidden.name = 'nim';
        nipHidden.name = '';

    } else if (role === 'dosen') {
        // Dosen
        nimLabel.textContent = 'NIP';
        nimInput.placeholder = 'Contoh: 198001012020121001';
        kalabWrapper.style.display = 'block';

        nipHidden.name = 'nip';
        nimHidden.name = '';

    } else if (role === 'teknisi') {
        // Teknisi
        nimLabel.textContent = 'NIP';
        nimInput.placeholder = 'Contoh: 198001012020121001';
        labWrapper.style.display = 'block'; // Tampilkan dropdown Lab
        nimInput.required = true;

        nipHidden.name = 'nip';
        nimHidden.name = '';

    } else if (role === 'ketua_lab') {
        // Ketua Lab
        nimLabel.textContent = 'NIP (Opsional)';
        nimInput.placeholder = 'Contoh: 198001012020121001';
        nimInput.required = false;

        nimHidden.name = '';
        nipHidden.name = '';
    }

    // Uncheck kalab jika role bukan dosen
    if (role !== 'dosen') {
        document.getElementById('is_kalab').checked = false;
    }

    generateEmail();
}

function generateEmail() {
    const role = document.getElementById('role').value;
    const nimNip = document.getElementById('nimNip').value.trim();
    const emailInput = document.getElementById('email');
    const passwordPreview = document.getElementById('passwordPreview');

    if (nimNip.length > 0) {
        if (role === 'mahasiswa') {
            emailInput.value = nimNip.toLowerCase() + '@student.polije.ac.id';
        } else {
            emailInput.value = nimNip.toLowerCase() + '@polije.ac.id';
        }
        passwordPreview.textContent = nimNip;
    } else {
        emailInput.value = '';
        passwordPreview.textContent = '[NIM/NIP]';
    }
}
</script>
@endsection
