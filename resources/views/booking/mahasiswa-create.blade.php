@extends('layouts.app')

@section('title', 'Form Peminjaman Laboratorium - Mahasiswa')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">🎓 Form Peminjaman Laboratorium</h1>
        <p class="text-gray-600">Ajukan permohonan penggunaan laboratorium untuk kegiatan akademik</p>
    </div>

    <!-- INFO: Alur Persetujuan untuk Mahasiswa -->
    <div class="mb-6 p-4 bg-orange-50 border border-orange-300 rounded-lg">
        <h4 class="font-semibold text-orange-800 mb-2">📋 Alur Persetujuan Booking Mahasiswa</h4>
        <div class="flex items-center gap-2 text-sm text-orange-700">
            <span class="flex items-center gap-1"><span class="w-6 h-6 rounded-full bg-orange-600 text-white flex items-center justify-center text-xs font-bold">1</span>Dosen</span>
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1"><span class="w-6 h-6 rounded-full bg-green-200 text-green-800 flex items-center justify-center text-xs font-bold">2</span>Teknisi</span>
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1"><span class="w-6 h-6 rounded-full bg-orange-600 text-white flex items-center justify-center text-xs font-bold">3</span>Ka Lab</span>
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="flex items-center gap-1"><span class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-bold">✓</span>Dikonfirmasi</span>
        </div>
        <p class="mt-2 text-xs text-orange-600">
            ⚠️ Booking Anda memerlukan persetujuan Dosen Pembimbing sebelum proses selanjutnya.
        </p>
    </div>

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

        <!-- SECTION 1: Data Mahasiswa Pemohon -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">👨‍🎓 Data Mahasiswa Pemohon</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $user->name }}" disabled class="w-full px-4 py-3 bg-gray-100 border rounded-lg text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NIM</label>
                    <input type="text" name="nim" value="{{ $user->nim }}" disabled class="w-full px-4 py-3 bg-gray-100 border rounded-lg text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prodi / Golongan</label>
                    <div class="flex gap-2">
                        <input type="text" name="prodi" value="{{ old('prodi', $user->prodi ?? 'Teknik Informatika') }}" class="w-full px-4 py-3 border rounded-lg @error('prodi') border-red-500 @enderror" required>
                        <input type="text" name="golongan" value="{{ old('golongan', $user->golongan ?? '-') }}" class="w-full px-4 py-3 border rounded-lg @error('golongan') border-red-500 @enderror" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">No HP / WhatsApp</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full px-4 py-3 border rounded-lg" required>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Detail Peminjaman -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🏢 Detail Peminjaman Laboratorium</h3>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium <span class="text-red-500">*</span></label>
                <select name="lab_name" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('lab_name') border-red-500 @enderror">
                    <option value="">-- Pilih Laboratorium --</option>
                    @foreach($labs as $lab)
                        <option value="{{ $lab }}" {{ old('lab_name', $prefilled['lab_name']) == $lab ? 'selected' : '' }}>
                            {{ $lab }}
                        </option>
                    @endforeach
                </select>
                @if(!empty($prefilled['lab_name']))
                    <p class="mt-1 text-xs text-blue-600">✅ Terpilih dari Dashboard</p>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Sesi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sesi <span class="text-red-500">*</span></label>
                    <select name="session" required id="sessionSelect" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('session') border-red-500 @enderror">
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
                            $currentSession = old('session', $prefilled['session'] ?? '');
                            $startTimeFromSession = $prefilled['start_time'] ?? '07:00';
                            $endTimeFromSession = $prefilled['end_time'] ?? '08:00';
                        @endphp
                        @foreach($sessionOptions as $sessionOption)
                            <option value="{{ $sessionOption }}" {{ $currentSession === $sessionOption ? 'selected' : '' }} data-start-time="{{ $startTimeFromSession }}" data-end-time="{{ $endTimeFromSession }}">
                                {{ $sessionOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('session') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Waktu disesuaikan dengan slot yang dipilih.</p>
                </div>

                <!-- Tanggal Booking -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="booking_date" required min="{{ date('Y-m-d') }}" value="{{ old('booking_date', $prefilled['booking_date'] ?? '') }}" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('booking_date') border-red-500 @enderror">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (Hari)</label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $prefilled['duration_days'] ?? 1) }}" min="1" max="30" class="w-full px-4 py-3 border rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Mulai (Otomatis)</label>
                    <input type="time" name="start_time" id="startTimeDisplay" readonly class="w-full px-4 py-3 bg-gray-100 border rounded-lg text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Selesai (Otomatis)</label>
                    <input type="time" name="end_time" id="endTimeDisplay" readonly class="w-full px-4 py-3 bg-gray-100 border rounded-lg text-gray-600">
                </div>
            </div>

            <input type="hidden" name="session_raw" id="sessionRaw"> <!-- Hidden field if backend needs exact string -->
        </div>

        <!-- SECTION 3: Kegiatan -->
        <div class="mb-8 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">📚 Jenis Kegiatan</h3>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <select name="activity" id="activitySelect" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('activity') border-red-500 @enderror">
                    <option value="">-- Pilih Jenis Kegiatan --</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity }}" {{ old('activity') == $activity ? 'selected' : '' }}>
                            {{ $activity }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="activityOtherSection" class="mb-6 {{ old('activity') === 'Lainnya' ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sebutkan Kegiatan Lainnya</label>
                <input type="text" name="activity_other" maxlength="255" value="{{ old('activity_other') }}" placeholder="Contoh: Workshop Machine Learning..." class="w-full px-4 py-3 border rounded-lg">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Kegiatan</label>
                <textarea name="purpose" rows="4" placeholder="Jelaskan detail kegiatan..." class="w-full px-4 py-3 border rounded-lg" required>{{ old('purpose') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kebutuhan Peralatan</label>
                <textarea name="equipment_needs" rows="2" placeholder="Jika diperlukan..." class="w-full px-4 py-3 border rounded-lg">{{ old('equipment_needs') }}</textarea>
            </div>
        </div>

        <!-- AGREEMENT -->
        <div class="mb-8 pb-6">
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6">
                <p class="text-sm text-gray-700 mb-4 font-medium">Dengan mengajukan permohonan ini, saya menyatakan:</p>

                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                        <p><strong>BERTANGGUNG JAWAB</strong> atas kegiatan dan mematuhi aturan laboratorium.</p>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                        <p><strong>MENGAWASI</strong> peserta kegiatan (jika ada) untuk menjaga kebersihan dan inventaris.</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agreement" required class="w-5 h-5 mt-0.5 text-blue-600 border-gray-300">
                        <span class="text-sm text-gray-700">
                            Saya setuju dan menerima semua pernyataan di atas.
                        </span>
                    </label>
                    @error('agreement')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
            <a href="{{ route('booking.index') }}" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">Batal</a>
            <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-md">🚀 Ajukan Peminjaman</button>
        </div>
    </form>
</div>

<!-- Scripts for Auto-Fill Logic -->
@push('scripts')
<script>
    const startTimeDisplay = document.getElementById('startTimeDisplay');
    const endTimeDisplay = document.getElementById('endTimeDisplay');
    const sessionSelect = document.getElementById('sessionSelect');

    // Function to update time display based on selected session
    function updateTimeDisplay() {
        const selectedOption = sessionSelect.options[sessionSelect.selectedIndex];
        if (selectedOption.value) {
            const start = selectedOption.getAttribute('data-start-time');
            const end = selectedOption.getAttribute('data-end-time');

            // Set hidden inputs if backend needs them too
            const hiddenStart = document.createElement('input');
            hiddenStart.type = 'hidden';
            hiddenStart.name = 'start_time'; // Make sure this matches store validation
            hiddenStart.id = 'start_time';
            hiddenStart.value = start;

            const hiddenEnd = document.createElement('input');
            hiddenEnd.type = 'hidden';
            hiddenEnd.name = 'end_time';
            hiddenEnd.id = 'end_time';
            hiddenEnd.value = end;

            // Replace if exist or append once
            if(!document.getElementById('start_time') && !document.getElementsByName('start_time')[0]) document.body.appendChild(hiddenStart);
            if(!document.getElementById('end_time') && !document.getElementsByName('end_time')[0]) document.body.appendChild(hiddenEnd);

            startTimeDisplay.value = start;
            endTimeDisplay.value = end;
        } else {
            startTimeDisplay.value = '';
            endTimeDisplay.value = '';
        }
    }

    // Trigger on page load to see prefilled value
    window.addEventListener('load', () => {
        // Check if dropdown already has a selected item (from prefilled)
        if (sessionSelect.options[sessionSelect.selectedIndex].value) {
             updateTimeDisplay();
        }
    });

    sessionSelect.addEventListener('change', updateTimeDisplay);

    // Toggle Lainnya section
    const activitySelect = document.getElementById('activitySelect');
    const otherSection = document.getElementById('activityOtherSection');

    activitySelect.addEventListener('change', function() {
        if (this.value === 'Lainnya') {
            otherSection.classList.remove('hidden');
            otherSection.querySelector('input').setAttribute('required', true);
        } else {
            otherSection.classList.add('hidden');
            const otherInput = otherSection.querySelector('input');
            if(otherInput) otherInput.removeAttribute('required');
        }
    });
</script>
@endpush

@endsection
