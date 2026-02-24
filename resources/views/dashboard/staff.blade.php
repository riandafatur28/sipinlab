@extends('layouts.app')

@section('title', 'Dashboard Staff - Polije')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Real-Time Clock -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-medium text-blue-100 mb-1">Waktu Sekarang</h2>
                <div class="text-3xl md:text-4xl font-bold" id="realtime-clock">00:00:00</div>
                <div class="text-blue-200 mt-1">
                    {{-- ✅ SELALU tampilkan hari & tanggal SEKARANG (tidak berubah) --}}
                    {{ $realtimeDayName }}, {{ $currentTime->isoFormat('D MMMM Y') }}
                </div>
            </div>
            <div class="hidden md:block">
                <svg class="w-20 h-20 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- ✅ Filter: Day + Lab Selector -->
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('dashboard.staff') }}" class="flex flex-wrap items-center gap-4">
            
            <!-- Day Selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">📅 Hari:</label>
                <select name="day" onchange="this.form.submit()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                    <option value="Senin" {{ $scheduleDayName == 'Senin' ? 'selected' : '' }}>Senin</option>
                    <option value="Selasa" {{ $scheduleDayName == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                    <option value="Rabu" {{ $scheduleDayName == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                    <option value="Kamis" {{ $scheduleDayName == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                    <option value="Jumat" {{ $scheduleDayName == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                </select>
            </div>

            <!-- Lab Selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">🏢 Lab:</label>
                <select name="lab" onchange="this.form.submit()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                    <option value="">Semua Lab</option>
                    @foreach($labs as $labName)
                        <option value="{{ $labName }}" {{ request('lab') == $labName ? 'selected' : '' }}>
                            {{ $labName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Info Text -->
            <span class="text-xs text-gray-500 ml-2">
                Menampilkan: <strong class="text-blue-600">{{ request('lab') ?: 'Semua Lab' }}</strong> 
                pada <strong class="text-blue-600">{{ $scheduleDayName }}</strong>
            </span>

            <!-- Reset Filter -->
            @if(request('day') || request('lab'))
            <a href="{{ route('dashboard.staff') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                Reset
            </a>
            @endif
        </form>
    </div>

    <!-- Legend / Keterangan Status -->
    <div class="mb-6 flex flex-wrap gap-3">
        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-4 h-4 rounded-full bg-green-500"></div>
            <span class="text-sm text-gray-700 font-medium">Tersedia (Bisa Booking)</span>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-4 h-4 rounded-full bg-yellow-500"></div>
            <span class="text-sm text-gray-700 font-medium">Proses Peminjaman</span>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-4 h-4 rounded-full bg-red-500"></div>
            <span class="text-sm text-gray-700 font-medium">Terisi/Digunakan</span>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-4 h-4 rounded-full bg-gray-400"></div>
            <span class="text-sm text-gray-700 font-medium">Selesai</span>
        </div>
    </div>

    <!-- Tables per Lab (Filtered) -->
    @php
        // Filter labs based on selected lab
        $displayLabs = request('lab') ? [request('lab')] : $labs;
    @endphp

    @foreach($displayLabs as $lab)
        @if(in_array($lab, $labs)) {{-- Ensure lab exists in database --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <!-- ✅ Header Lab dengan Hari Bold & Besar (pakai scheduleDayName) -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">{{ $lab }}</h2>
                <span class="text-lg font-extrabold text-white bg-blue-600 px-4 py-1.5 rounded-lg shadow-sm">
                    {{ $scheduleDayName }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($scheduleData[$lab] ?? [] as $item)
                        <tr class="hover:bg-gray-50 transition-colors {{ $item['is_break'] ? 'bg-gray-100' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item['no'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['session'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-mono">
                                {{ $item['start'] }} - {{ $item['end'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($item['is_break'])
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-300 text-gray-700">
                                        Istirahat
                                    </span>
                                @else
                                    <!-- ✅ Status dengan warna dinamis berdasarkan status_color -->
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full cursor-pointer transition-transform hover:scale-105
                                        @if($item['status_color'] === 'green') 
                                            bg-green-100 text-green-800 border border-green-300
                                        @elseif($item['status_color'] === 'yellow') 
                                            bg-yellow-100 text-yellow-800 border border-yellow-300
                                        @elseif($item['status_color'] === 'red') 
                                            bg-red-100 text-red-800 border border-red-300
                                        @else 
                                            bg-gray-400 text-gray-100 @endif"
                                        onclick="showStatusInfo('{{ $lab }}', '{{ $item['session'] }}', '{{ $item['status_label'] }}', '{{ $item['status_color'] }}', '{{ addslashes($item['booking_info'] ?? '') }}')">
                                        {{ $item['status_label'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @if(!$item['is_break'])
                                    @if($item['status'] === 'tersedia')
                                        <button onclick="openBookingModal('{{ $lab }}', '{{ $item['session'] }}', '{{ $item['start'] }}', '{{ $item['end'] }}')"
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                            📅 Booking
                                        </button>
                                    @elseif($item['status'] === 'proses')
                                        <span class="text-xs text-yellow-600 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Proses
                                        </span>
                                    @elseif($item['status'] === 'terisi')
                                        <span class="text-xs text-red-600 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Terisi
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">Selesai</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endforeach

    @if(empty($displayLabs) || (request('lab') && !in_array(request('lab'), $labs)))
    <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
        <p class="text-gray-500">Laboratorium tidak ditemukan.</p>
        <a href="{{ route('dashboard.staff') }}" class="text-blue-600 hover:underline mt-2 inline-block">Reset filter</a>
    </div>
    @endif

</div>

<!-- Status Info Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">Informasi Status</h3>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <p class="text-sm text-gray-500">Laboratorium</p>
                <p class="font-semibold text-gray-800" id="modalLab">-</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Sesi</p>
                <p class="font-semibold text-gray-800" id="modalSession">-</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="font-semibold" id="modalStatus">-</p>
            </div>
            <div id="modalInfo" class="text-sm text-gray-600 italic hidden"></div>
            <div id="modalMessage" class="mt-4 p-3 rounded-lg bg-blue-50 text-sm text-blue-800"></div>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeStatusModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">📅 Booking Laboratorium</h3>
            <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="bookingForm" onsubmit="submitBooking(event)">
            @csrf
            <input type="hidden" id="bookingLab" name="lab">
            <input type="hidden" id="bookingSession" name="session">

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Laboratorium</p>
                    <p class="font-semibold text-gray-800" id="formLab">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Sesi</p>
                    <p class="font-semibold text-gray-800" id="formSession">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Waktu</p>
                    <p class="font-semibold text-gray-800" id="formTime">-</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan</label>
                    <textarea name="purpose" rows="3" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="Jelaskan keperluan penggunaan lab..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeBookingModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                    ✅ Konfirmasi Booking
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ✅ Auto-refresh dashboard ketika ada booking baru/approval
document.addEventListener('DOMContentLoaded', function() {
    // Refresh jika ada param ?refresh=1 (dari redirect setelah booking)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('refresh')) {
        // Hapus param refresh dari URL agar tidak refresh terus
        urlParams.delete('refresh');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, newUrl);
        
        // Reload halaman untuk fetch data terbaru
        setTimeout(() => window.location.reload(), 500);
    }
    
    // ✅ Optional: Polling setiap 30 detik untuk cek update
    // setInterval(() => {
    //     if (document.visibilityState === 'visible') {
    //         fetch(window.location.href + '?ajax=1')
    //             .then(res => res.text())
    //             .then(html => {
    //                 // Update hanya bagian schedule, bukan seluruh halaman
    //                 // Implementasi bisa disesuaikan dengan struktur view Anda
    //             });
    //     }
    // }, 30000);
});
</script>
@endpush
@endsection

@push('scripts')
<script>
// Real-time clock update
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    
    document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;
}

setInterval(updateClock, 1000);
updateClock();

// Status modal with booking info support
function showStatusInfo(lab, session, status, color, bookingInfo = '') {
    document.getElementById('modalLab').textContent = lab;
    document.getElementById('modalSession').textContent = session;
    document.getElementById('modalStatus').textContent = status;
    
    const infoEl = document.getElementById('modalInfo');
    const messageEl = document.getElementById('modalMessage');
    const statusEl = document.getElementById('modalStatus');
    
    // Show booking info if available
    if (bookingInfo && bookingInfo.trim() !== '') {
        infoEl.textContent = '📋 ' + bookingInfo;
        infoEl.classList.remove('hidden');
    } else {
        infoEl.classList.add('hidden');
    }
    
    // Set message and color based on status
    let message = '';
    if (color === 'green') {
        message = '✅ Laboratorium tersedia untuk booking. Silakan klik tombol "Booking" untuk memesan.';
        statusEl.className = 'font-semibold text-green-600';
    } else if (color === 'yellow') {
        message = '⏳ Sesi ini sedang berlangsung. Tidak dapat dibooking.';
        statusEl.className = 'font-semibold text-yellow-600';
    } else if (color === 'red') {
        message = '❌ Laboratorium sudah terisi/digunakan pada sesi ini. Silakan pilih sesi lain yang tersedia.';
        statusEl.className = 'font-semibold text-red-600';
    } else {
        message = '⏹️ Sesi ini sudah selesai.';
        statusEl.className = 'font-semibold text-gray-600';
    }
    
    messageEl.textContent = message;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// Booking modal
function openBookingModal(lab, session, start, end) {
    document.getElementById('bookingLab').value = lab;
    document.getElementById('bookingSession').value = session;
    document.getElementById('formLab').textContent = lab;
    document.getElementById('formSession').textContent = session;
    document.getElementById('formTime').textContent = start + ' - ' + end;
    document.getElementById('bookingModal').classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('bookingForm').reset();
}

// Submit booking via AJAX
async function submitBooking(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('{{ route("booking.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            closeBookingModal();
            location.reload();
        } else {
            alert('❌ Terjadi kesalahan: ' + (result.message || 'Silakan coba lagi'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan saat melakukan booking');
    }
}

// Close modals when clicking outside
document.getElementById('statusModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});

document.getElementById('bookingModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBookingModal();
});
</script>
@endpush