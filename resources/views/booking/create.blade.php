@extends('layouts.app')

@section('title', 'Form Booking Laboratorium - Mahasiswa')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">📅 Form Booking Laboratorium</h1>
        <p class="text-gray-600">Ajukan permohonan penggunaan laboratorium untuk kegiatan akademik</p>
    </div>

    <!-- INFO: Alur Persetujuan untuk Mahasiswa -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-300 rounded-lg">
        <h4 class="font-semibold text-blue-800 mb-2">📋 Alur Persetujuan Booking Mahasiswa</h4>
        <div class="flex items-center gap-2 text-sm text-blue-700">
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                Dosen
            </span>
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center text-xs font-bold">2</span>
                Teknisi
            </span>
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center text-xs font-bold">3</span>
                Ka Lab
            </span>
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                Dikonfirmasi
            </span>
        </div>
        <p class="mt-2 text-xs text-blue-600">
            ✅ Booking Anda akan melalui 3 tahap persetujuan (Dosen → Teknisi → Ka Lab) sebelum dikonfirmasi.
        </p>
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

    <form action="{{ route('booking.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf

        <!-- SECTION 1: Data Mahasiswa Pemohon (Auto-fill) -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">👤 Data Mahasiswa Pemohon</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" value="{{ $user->name }}" disabled
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                </div>

                <!-- NIM -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NIM</label>
                    <input type="text" value="{{ $user->nim ?? '-' }}" disabled
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">Nomor Induk Mahasiswa</p>
                </div>

                <!-- Program Studi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                    <input type="text" name="prodi" value="Teknik Informatika" readonly
                           class="w-full px-4 py-3 bg-blue-50 border border-blue-300 rounded-lg text-blue-800 font-medium">
                    <p class="mt-1 text-xs text-gray-500">Otomatis: Teknik Informatika</p>
                </div>

                <!-- Golongan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Golongan <span class="text-red-500">*</span></label>
                    <select name="golongan" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('golongan') border-red-500 @enderror">
                        <option value="">-- Pilih Golongan --</option>
                        <option value="A" {{ old('golongan', $user->golongan ?? '') == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('golongan', $user->golongan ?? '') == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ old('golongan', $user->golongan ?? '') == 'C' ? 'selected' : '' }}>C</option>
                    </select>
                    @error('golongan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Golongan praktikum Anda</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Institusi</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                </div>

                <!-- No. Telepon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" required maxlength="20"
                           value="{{ old('phone', $user->phone ?? '') }}"
                           placeholder="Contoh: 081234567890"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: Detail Booking -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🏢 Detail Booking Laboratorium</h3>
            
            <!-- Toggle: Individu / Kelompok -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_group" id="isGroupToggle" value="1" 
                           class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                           {{ old('is_group') ? 'checked' : '' }}>
                    <span class="font-medium text-gray-700">Booking Kelompok</span>
                </label>
                <p class="mt-2 text-sm text-gray-500 ml-8">
                    Centang jika booking untuk kelompok. Jika tidak, booking untuk individu.
                </p>
            </div>

            <!-- Anggota Kelompok -->
            <div id="groupSection" class="{{ old('is_group') ? '' : 'hidden' }} mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="font-medium text-blue-800 mb-3">👥 Anggota Kelompok</h4>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cari Anggota (by NIM/Nama)</label>
                    <div class="relative">
                        <input type="text" id="memberSearch" placeholder="Ketik NIM atau nama..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <div id="searchResults" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden"></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 2 karakter untuk mencari</p>
                </div>

                <div id="selectedMembers" class="space-y-2">
                    @if(old('members'))
                        @foreach(old('members') as $memberId)
                            @php
                                $member = $students->firstWhere('id', $memberId);
                            @endphp
                            @if($member)
                            <div class="flex items-center justify-between p-2 bg-white rounded border border-blue-200">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                                        {{ substr($member->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $member->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $member->nim ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <button type="button" onclick="removeMember({{ $member->id }})" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <input type="hidden" name="members[]" value="{{ $member->id }}">
                            </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Dosen Pembimbing -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dosen Pembimbing (Opsional)</label>
                <select name="supervisor_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('supervisor_id') border-red-500 @enderror">
                    <option value="">-- Pilih Dosen Pembimbing --</option>
                    @foreach($dosens as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('supervisor_id') == $dosen->id ? 'selected' : '' }}>
                            {{ $dosen->name }}
                        </option>
                    @endforeach
                </select>
                @error('supervisor_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Laboratorium -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium <span class="text-red-500">*</span></label>
                <select name="lab_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('lab_name') border-red-500 @enderror">
                    <option value="">-- Pilih Laboratorium --</option>
                    @foreach($labs as $lab)
                        <option value="{{ $lab }}" {{ old('lab_name', $prefilled['lab_name'] ?? '') == $lab ? 'selected' : '' }}>
                            {{ $lab }}
                        </option>
                    @endforeach
                </select>
                @error('lab_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @if(!empty($prefilled['lab_name']))
                    <p class="mt-1 text-xs text-blue-600">✅ Diisi otomatis dari dashboard</p>
                @endif
            </div>

            <!-- Sesi & Tanggal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Sesi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sesi <span class="text-red-500">*</span></label>
                    <select name="session" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('session') border-red-500 @enderror">
                        <option value="">-- Pilih Sesi --</option>
                        @php
                            $sessionOptions = [
                                'Sesi 1 (07:00 - 08:00)',
                                'Sesi 2 (08:00 - 09:00)',
                                'Sesi 3 (09:00 - 10:00)',
                                'Sesi 4 (10:00 - 11:00)',
                                'Sesi 5 (13:00 - 14:00)',
                                'Sesi 6 (14:00 - 15:00)',
                                'Sesi 7 (15:00 - 16:00)',
                                'Sesi 8 (16:00 - 17:00)',
                            ];
                            $currentSession = old('session') ?? $prefilled['session'] ?? '';
                        @endphp
                        @foreach($sessionOptions as $sessionOption)
                            <option value="{{ $sessionOption }}" 
                                    {{ $currentSession === $sessionOption ? 'selected' : '' }}>
                                {{ $sessionOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('session') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Tanggal Booking -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="booking_date" required
                           value="{{ old('booking_date', $prefilled['booking_date'] ?? date('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('booking_date') border-red-500 @enderror">
                    @error('booking_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Durasi & Tanggal Range (Readonly - Auto-calculated) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Durasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (Hari) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_days" required min="1" max="30" value="{{ old('duration_days', 1) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('duration_days') border-red-500 @enderror">
                </div>

                <!-- Start Date (Readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" id="startDate" readonly
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">Sama dengan tanggal booking</p>
                </div>

                <!-- End Date (Readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" id="endDate" readonly
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">Otomatis dihitung dari durasi</p>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Kegiatan & Keperluan -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Kegiatan & Keperluan</h3>
            
            <!-- Activity -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <select name="activity" id="activitySelect" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('activity') border-red-500 @enderror">
                    <option value="">-- Pilih Kegiatan --</option>
                    @foreach($activities['mahasiswa'] as $activity)
                        <option value="{{ $activity }}" {{ old('activity') == $activity ? 'selected' : '' }}>
                            {{ $activity }}
                        </option>
                    @endforeach
                </select>
                @error('activity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Activity Other -->
            <div id="activityOtherSection" class="mb-6 {{ old('activity') === 'Lainnya' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sebutkan Kegiatan Lainnya <span class="text-red-500">*</span></label>
                <input type="text" name="activity_other" maxlength="255"
                       value="{{ old('activity_other') }}"
                       placeholder="Contoh: Workshop Machine Learning"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('activity_other') border-red-500 @enderror">
                @error('activity_other') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Purpose -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan / Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="purpose" required rows="4" maxlength="1000"
                          placeholder="Jelaskan keperluan penggunaan laboratorium secara detail..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                @error('purpose') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-500">Maksimal 1000 karakter</p>
            </div>
        </div>

        <!-- SECTION 4: Pernyataan Persetujuan -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Pernyataan Persetujuan</h3>
            
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6">
                <p class="text-sm text-gray-700 mb-4 font-medium">Dengan mengajukan permohonan ini, saya selaku mahasiswa menyatakan:</p>
                
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                        <p><strong>BERTANGGUNG JAWAB DAN MEMATUHI ATURAN</strong> yang ditetapkan pihak kampus terkait dengan penggunaan ruangan.</p>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                        <p><strong>BERSEDIA MENJAGA KETERATURAN, KEBERSIHAN, DAN INVENTARIS</strong> ruangan selama melaksanakan kegiatan di dalam ruangan.</p>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                        <p><strong>BERSEDIA DIKENAKAN SANKSI</strong> apabila dalam pelaksanaannya dinilai dan terbukti melanggar poin 1 dan poin 2.</p>
                    </div>
                </div>
                
                <!-- Checkbox Persetujuan -->
                <div class="mt-6 p-4 bg-white border border-yellow-400 rounded-lg">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agreement" id="agreement" required
                               class="w-5 h-5 mt-0.5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                        <span class="text-sm text-gray-700">
                            <strong>Saya setuju dan menerima semua pernyataan di atas</strong> serta bersedia mematuhi seluruh peraturan yang berlaku.
                        </span>
                    </label>
                    @error('agreement')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <a href="{{ route('booking.index') }}" 
               class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                Batal
            </a>
            <button type="submit" id="submitBtn" disabled
                    class="px-8 py-3 bg-gray-400 text-white rounded-lg font-medium transition-colors shadow-lg flex items-center gap-2 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Ajukan Booking
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
// Toggle Group Section
document.getElementById('isGroupToggle')?.addEventListener('change', function() {
    document.getElementById('groupSection').classList.toggle('hidden', !this.checked);
});

// Activity "Lainnya" toggle
document.getElementById('activitySelect')?.addEventListener('change', function() {
    const otherSection = document.getElementById('activityOtherSection');
    if (this.value === 'Lainnya') {
        otherSection.classList.remove('hidden');
    } else {
        otherSection.classList.add('hidden');
        document.querySelector('[name="activity_other"]').value = '';
    }
});

// Auto-calculate date range (for display only)
const bookingDateInput = document.querySelector('[name="booking_date"]');
const durationInput = document.querySelector('[name="duration_days"]');
const startDateInput = document.getElementById('startDate');
const endDateInput = document.getElementById('endDate');

function calculateDateRange() {
    const bookingDate = bookingDateInput?.value;
    const duration = parseInt(durationInput?.value) || 1;
    
    if (bookingDate && duration) {
        startDateInput.value = bookingDate;
        const start = new Date(bookingDate);
        start.setDate(start.getDate() + duration - 1);
        endDateInput.value = start.toISOString().split('T')[0];
    }
}

bookingDateInput?.addEventListener('change', calculateDateRange);
durationInput?.addEventListener('input', calculateDateRange);
calculateDateRange();

// Search Members
const memberSearchInput = document.getElementById('memberSearch');
const searchResults = document.getElementById('searchResults');
let searchTimeout;

memberSearchInput?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        searchResults.classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(async () => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = `{{ route('booking.search-users') }}?query=${encodeURIComponent(query)}&type=student`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                }
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const members = await response.json();
            
            if (members.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">Tidak ditemukan</div>';
            } else {
                searchResults.innerHTML = members.map(member => `
                    <div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0"
                         onclick="addMember(${member.id}, '${member.name.replace(/'/g, "\\'")}', '${member.nim || 'N/A'}')">
                        <div class="font-medium text-gray-800">${member.name}</div>
                        <div class="text-xs text-gray-500">${member.nim || 'N/A'}</div>
                    </div>
                `).join('');
            }
            searchResults.classList.remove('hidden');
        } catch (error) {
            searchResults.innerHTML = `<div class="p-3 text-sm text-red-500">Error: ${error.message}</div>`;
            searchResults.classList.remove('hidden');
        }
    }, 300);
});

function addMember(id, name, nim) {
    const selectedDiv = document.getElementById('selectedMembers');
    if (document.querySelector(`[name="members[]"][value="${id}"]`)) {
        alert('Anggota sudah ditambahkan');
        return;
    }
    
    const html = `
        <div class="flex items-center justify-between p-2 bg-white rounded border border-blue-200" id="member-${id}">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">${name.charAt(0).toUpperCase()}</div>
                <div><div class="font-medium text-gray-800">${name}</div><div class="text-xs text-gray-500">${nim}</div></div>
            </div>
            <button type="button" onclick="removeMember(${id})" class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <input type="hidden" name="members[]" value="${id}">
        </div>
    `;
    
    selectedDiv.insertAdjacentHTML('beforeend', html);
    memberSearchInput.value = '';
    searchResults.classList.add('hidden');
}

function removeMember(id) {
    document.getElementById(`member-${id}`)?.remove();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#memberSearch') && !e.target.closest('#searchResults')) {
        searchResults?.classList.add('hidden');
    }
});

// Enable/disable submit button
const agreementCheckbox = document.getElementById('agreement');
const submitBtn = document.getElementById('submitBtn');

agreementCheckbox?.addEventListener('change', function() {
    if (this.checked) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
    }
});
</script>
@endpush

@endsection