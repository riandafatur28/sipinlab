@extends('layouts.app')

@section('title', 'Tambah User - Admin')

@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah User Baru</h1>
        <p class="text-gray-600">Buat akun untuk mahasiswa, dosen, teknisi, atau ka lab</p>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf

        <!-- Role Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Role / Jabatan <span class="text-red-500">*</span>
            </label>
            <select name="role" id="role" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    onchange="updatePlaceholder()">
                <option value="">-- Pilih Role --</option>
                <option value="mahasiswa">🎓 Mahasiswa</option>
                <option value="dosen">👨‍🏫 Dosen</option>
                <option value="teknisi">🔧 Teknisi</option>
                <option value="ketua_lab">👨‍💼 Ketua Laboratorium</option>
            </select>
        </div>

        <!-- Nama Lengkap -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" required
                   placeholder="Contoh: Azka Riswanda"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- NIM/NIP -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span id="nimLabel">NIM</span> / NIP <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nim_nip" id="nimNip" required
                   placeholder="Contoh: e41231605"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   oninput="generateEmail()">
            <p class="mt-2 text-sm text-gray-500">
                💡 <strong>Password default</strong> akan dibuat dari NIM/NIP ini
            </p>
        </div>

        <!-- Email (Auto-generated) -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" id="email" required
                   placeholder="nim@domain.polije.ac.id"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50">
            <p class="mt-2 text-sm text-blue-600">
                📧 Email akan otomatis terisi berdasarkan NIM/NIP
            </p>
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
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.users.index') }}"
               class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">
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
function updatePlaceholder() {
    const role = document.getElementById('role').value;
    const nimLabel = document.getElementById('nimLabel');
    const nimInput = document.getElementById('nimNip');

    if (role === 'mahasiswa') {
        nimLabel.textContent = 'NIM';
        nimInput.placeholder = 'Contoh: e41231605';
    } else {
        nimLabel.textContent = 'NIP';
        nimInput.placeholder = 'Contoh: 198001012020121001';
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
            emailInput.value = nimNip + '@student.polije.ac.id';
        } else {
            emailInput.value = nimNip + '@polije.ac.id';
        }
        passwordPreview.textContent = nimNip;
    } else {
        emailInput.value = '';
        passwordPreview.textContent = '[NIM/NIP]';
    }
}
</script>
@endsection
