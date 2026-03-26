@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

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

    <!-- ✅ Filter: Calendar + Day + Lab + Kalab View Mode Toggle -->
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('dashboard.staff') }}" class="flex flex-wrap items-center gap-4" id="filterForm">

            <!-- ✅ Calendar Date Picker (UTAMA) -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">📅 Tanggal:</label>
                <input type="date"
                       id="datePicker"
                       name="date"
                       value="{{ $scheduleDate ?? date('Y-m-d') }}"
                       min="{{ date('Y-m-d') }}"
                       max="{{ date('Y-m-d', strtotime('+90 days')) }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium text-sm"
                       onchange="onDateChange(this.value)">
            </div>

            <!-- Day Selector (Auto-sync dengan tanggal) -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Hari:</label>
                <select name="day" id="daySelect" onchange="onDayChange(this.value)"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $day)
                        <option value="{{ $day }}" {{ $scheduleDayName == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
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

            <!-- ✅ Toggle View Mode Khusus Kalab -->
            @if(Auth::user()->isKalab())
            <div class="flex items-center gap-2 ml-auto pl-4 border-l border-gray-200">
                <label class="text-sm font-medium text-gray-700">👁️ Tampilan:</label>
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button type="button"
                            onclick="toggleViewMode('dosen')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ session('dashboard_view_mode', 'kalab') !== 'kalab' ? 'bg-white text-blue-600 shadow' : 'text-gray-500 hover:text-gray-700' }}">
                        🎓 Dosen
                    </button>
                    <button type="button"
                            onclick="toggleViewMode('kalab')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ session('dashboard_view_mode', 'kalab') === 'kalab' ? 'bg-white text-indigo-600 shadow' : 'text-gray-500 hover:text-gray-700' }}">
                        👔 Kalab
                    </button>
                </div>
            </div>
            @endif

            <!-- Info Text -->
            <span class="text-xs text-gray-500 ml-2">
                Menampilkan: <strong class="text-blue-600">{{ request('lab') ?: 'Semua Lab' }}</strong>
                pada <strong class="text-blue-600">{{ $scheduleDayName }}, {{ Carbon::parse($scheduleDate)->isoFormat('D MMM Y') }}</strong>
                @if(Auth::user()->isKalab())
                    | Mode: <strong class="{{ session('dashboard_view_mode', 'kalab') === 'kalab' ? 'text-indigo-600' : 'text-blue-600' }}">
                        {{ session('dashboard_view_mode', 'kalab') === 'kalab' ? '👔 Kalab' : '🎓 Dosen' }}
                    </strong>
                @endif
            </span>

            <!-- Reset Filter -->
            @if(request('date') || request('day') || request('lab'))
            <a href="{{ route('dashboard.staff') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                🔄 Reset
            </a>
            @endif
        </form>
    </div>

    <!-- ✅ Stats Dashboard untuk Kalab View -->
    @if($isKalabView && !empty($stats))
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                    <span class="text-orange-600 text-lg">⏳</span>
                </div>
                <div>
                    <p class="text-xs text-orange-600 font-medium">Menunggu Dosen</p>
                    <p class="text-xl font-bold text-orange-800">{{ $stats['pending_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <span class="text-blue-600 text-lg">✅</span>
                </div>
                <div>
                    <p class="text-xs text-blue-600 font-medium">Disetujui Dosen</p>
                    <p class="text-xl font-bold text-blue-800">{{ $stats['approved_dosen_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <span class="text-indigo-600 text-lg">🔧</span>
                </div>
                <div>
                    <p class="text-xs text-indigo-600 font-medium">Disetujui Teknisi</p>
                    <p class="text-xl font-bold text-indigo-800">{{ $stats['approved_teknisi_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <span class="text-green-600 text-lg">🎉</span>
                </div>
                <div>
                    <p class="text-xs text-green-600 font-medium">Confirmed</p>
                    <p class="text-xl font-bold text-green-800">{{ $stats['confirmed_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

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
        @if($isKalabView)
        <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg shadow-sm border border-orange-200">
            <div class="w-4 h-4 rounded-full bg-orange-500"></div>
            <span class="text-sm text-gray-700 font-medium">Menunggu Approval</span>
        </div>
        @endif
    </div>

    <!-- ✅ Loading Indicator (untuk AJAX calendar) -->
    <div id="loadingIndicator" class="hidden mb-6 text-center py-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memuat jadwal...</span>
        </div>
    </div>

    <!-- Tables per Lab (Filtered) -->
    @php
        $displayLabs = request('lab') ? [request('lab')] : $labs;
        $dayMap = [
            'Senin' => 'Monday', 'Selasa' => 'Tuesday', 'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday', 'Jumat' => 'Friday', 'Sabtu' => 'Saturday', 'Minggu' => 'Sunday',
        ];
    @endphp

    <div id="scheduleContainer">
        @foreach($displayLabs as $lab)
            @if(in_array($lab, $labs))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6" data-lab="{{ $lab }}">
                <!-- Header Lab -->
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
                        <tbody class="divide-y divide-gray-200" id="scheduleBody-{{ Str::slug($lab) }}">
                            @foreach($scheduleData[$lab] ?? [] as $item)
                            <tr class="hover:bg-gray-50 transition-colors {{ $item['is_break'] ? 'bg-gray-100' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item['no'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['session'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-mono">
                                    {{ $item['start'] }} - {{ $item['end'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($item['is_break'])
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-300 text-gray-700">Istirahat</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full cursor-pointer transition-transform hover:scale-105
                                            @if($item['status_color'] === 'green') bg-green-100 text-green-800 border border-green-300
                                            @elseif($item['status_color'] === 'yellow') bg-yellow-100 text-yellow-800 border border-yellow-300
                                            @elseif($item['status_color'] === 'red') bg-red-100 text-red-800 border border-red-300
                                            @elseif($item['status_color'] === 'orange') bg-orange-100 text-orange-800 border border-orange-300
                                            @elseif($item['status_color'] === 'blue') bg-blue-100 text-blue-800 border border-blue-300
                                            @elseif($item['status_color'] === 'indigo') bg-indigo-100 text-indigo-800 border border-indigo-300
                                            @else bg-gray-400 text-gray-100 @endif"
                                            onclick="showStatusInfo('{{ $lab }}', '{{ $item['session'] }}', '{{ $item['status_label'] }}', '{{ $item['status_color'] }}', '{{ addslashes($item['booking_info'] ?? '') }}')">
                                            {{ $item['status_label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if(!$item['is_break'])
                                        @if($item['status'] === 'tersedia')
                                            <button onclick="openBookingModal('{{ $lab }}', '{{ $item['session'] }}', '{{ $item['start'] }}', '{{ $item['end'] }}', '{{ $scheduleDate }}')"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                                📅 Booking
                                            </button>
                                        @elseif(in_array($item['status'], ['pending', 'approved_dosen', 'approved_teknisi']) && $isKalabView)
                                            {{-- Kalab view: tampilkan tombol approve untuk booking pending --}}
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs text-gray-500">{{ $item['booking_info'] ?? '' }}</span>
                                                @if($item['status'] === 'approved_teknisi')
                                                    <form action="{{ route('booking.approve-kalab', $item['booking_id']) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-medium">
                                                            👔 Approve Kalab
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-gray-400">Menunggu approval...</span>
                                                @endif
                                            </div>
                                        @elseif($item['status'] === 'proses')
                                            <span class="text-xs text-yellow-600 font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"></path>
                                                </svg>
                                                Proses
                                            </span>
                                        @elseif($item['status'] === 'terisi')
                                            <span class="text-xs text-red-600 font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
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
            <div><p class="text-sm text-gray-500">Laboratorium</p><p class="font-semibold text-gray-800" id="modalLab">-</p></div>
            <div><p class="text-sm text-gray-500">Sesi</p><p class="font-semibold text-gray-800" id="modalSession">-</p></div>
            <div><p class="text-sm text-gray-500">Status</p><p class="font-semibold" id="modalStatus">-</p></div>
            <div id="modalInfo" class="text-sm text-gray-600 italic hidden"></div>
            <div id="modalMessage" class="mt-4 p-3 rounded-lg bg-blue-50 text-sm text-blue-800"></div>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeStatusModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">Tutup</button>
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
            <input type="hidden" id="bookingLab" name="lab_name">
            <input type="hidden" id="bookingSession" name="session">
            <input type="hidden" id="bookingStartTime" name="start_time">
            <input type="hidden" id="bookingEndTime" name="end_time">

            <div class="space-y-4">
                <div><p class="text-sm text-gray-500">Laboratorium</p><p class="font-semibold text-gray-800" id="formLab">-</p></div>
                <div><p class="text-sm text-gray-500">Sesi</p><p class="font-semibold text-gray-800" id="formSession">-</p></div>
                <div><p class="text-sm text-gray-500">Waktu</p><p class="font-semibold text-gray-800" id="formTime">-</p></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="booking_date" id="bookingDate" required
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
                <button type="button" onclick="closeBookingModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">✅ Konfirmasi Booking</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ✅ Auto-refresh dashboard ketika ada booking baru/approval
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('refresh')) {
        urlParams.delete('refresh');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, newUrl);
        setTimeout(() => window.location.reload(), 500);
    }
});

// Real-time clock
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);
updateClock();

// ✅ Mapping hari Indonesia ke Inggris untuk Carbon (Digunakan backend/sesuai kebutuhan lain)
const dayMapToEnglish = {
    'Senin': 'Monday', 'Selasa': 'Tuesday', 'Rabu': 'Wednesday',
    'Kamis': 'Thursday', 'Jumat': 'Friday', 'Sabtu': 'Saturday', 'Minggu': 'Sunday'
};

// ✅ Mapping Hari Indonesia ke Integer Sesuai Standar JavaScript getDay()
// JavaScript getDay(): 0=Minggu, 1=Senin, ..., 6=Sabtu
const jsDayIndexMap = {
    'Minggu': 0,
    'Senin': 1,
    'Selasa': 2,
    'Rabu': 3,
    'Kamis': 4,
    'Jumat': 5,
    'Sabtu': 6
};

// ✅ Array nama hari untuk akses cepat via index getDay()
const jSDayNamesArray = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];


// ✅ Fungsi: Ketika tanggal diubah di datepicker → Update Dropdown Hari
function onDateChange(dateValue) {
    if (!dateValue) return;
    // Tambahkan zona waktu agar tidak terpengaruh offset lokal browser terlalu ekstrem
    const date = new Date(dateValue + 'T00:00:00');

    // Ambil index sesuai standar JS (0-6)
    const jsIndex = date.getDay();
    // Ambil nama dari array berdasarkan index tersebut
    const dayName = jSDayNamesArray[jsIndex];

    document.getElementById('daySelect').value = dayName;
    document.getElementById('filterForm').submit();
}

/**
 * ✅ FIXED: Fungsi: Ketika hari diubah di select → Cari Tanggal Terdekat (Correct Logic)
 */
function onDayChange(dayName) {
    const today = new Date();

    // ✅ Ambil angka hari target menggunakan map standar JS (0-6)
    const targetDayNum = jsDayIndexMap[dayName];

    // Jika nama hari tidak dikenali, fallback ke hari ini
    if (targetDayNum === undefined) {
        console.error('Unknown day name:', dayName);
        return;
    }

    // Ambil angka hari hari ini (0=Minggu...6=Sabtu)
    const todayDayNum = today.getDay();

    // Hitung selisih
    let diff = targetDayNum - todayDayNum;

    // ❌ LOGIKA SALAH (Oleh karena itu muncul 1 hari kurang):
    // if (diff <= 0) diff += 7;
    // Ini akan menjadikannya besok jika memilih hari yang sama (misal pilih Senin saat ini Senin)
    // diff = 0 -> +7 = 7. Hasilnya 1 minggu depan (Salah).

    // ✅ LOGIKA BENAR:
    // Jika hasil pengurangan negatif, berarti hari yang dipilih sudah lewat minggu ini.
    if (diff < 0) {
        diff += 7;
    }
    // Jika diff positif atau nol (hari sama), biarkan tetap begitu.
    // Namun jika user memilih hari SAMA DENGAN HARI INI, user mungkin ingin menampilkan hari INI juga.
    // Jadi kondisi diff < 0 adalah kuncinya. Jangan tambah 7 jika diff >= 0.

    // Contoh: Hari ini Jumat (5), Pilih Senin (1)
    // diff = 1 - 5 = -4. -4 + 7 = 3. Jumat + 3 hari = Senin (Benar).

    // Contoh: Hari ini Senin (1), Pilih Senin (1)
    // diff = 1 - 1 = 0. diff tidak < 0. Tetap 0. Senin + 0 = Senin (Benar).

    const targetDate = new Date(today);
    targetDate.setDate(today.getDate() + diff);

    const yyyy = targetDate.getFullYear();
    const mm = String(targetDate.getMonth() + 1).padStart(2, '0');
    const dd = String(targetDate.getDate()).padStart(2, '0');

    document.getElementById('datePicker').value = `${yyyy}-${mm}-${dd}`;
    document.getElementById('filterForm').submit();
}

// ✅ Fungsi: Toggle View Mode untuk Kalab (AJAX)
function toggleViewMode(mode) {
    // Optimistic UI update
    document.querySelectorAll('[onclick*="toggleViewMode"]').forEach(btn => {
        btn.classList.remove('bg-white', 'text-blue-600', 'text-indigo-600', 'shadow');
        btn.classList.add('text-gray-500');
    });
    event.currentTarget.classList.remove('text-gray-500');
    event.currentTarget.classList.add('bg-white', mode === 'kalab' ? 'text-indigo-600' : 'text-blue-600', 'shadow');

    // AJAX request
    fetch("{{ route('dashboard.toggle-view-mode') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ mode })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.href = data.redirect, 800);
        } else {
            showToast('Gagal mengubah mode', 'error');
            location.reload();
        }
    })
    .catch(err => {
        console.error('Toggle error:', err);
        showToast('Terjadi kesalahan', 'error');
        location.reload();
    });
}

// ✅ Fungsi: Load jadwal via AJAX (opsional, untuk UX lebih smooth)
async function loadScheduleByDate(date, lab = '') {
    const loading = document.getElementById('loadingIndicator');

    try {
        loading.classList.remove('hidden');

        const response = await fetch("{{ route('dashboard.schedule-by-date') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ date, lab })
        });

        const data = await response.json();

        if (data.success) {
            updateScheduleUI(data.schedule, data.day_name, data.date);
            const url = new URL(window.location);
            url.searchParams.set('date', data.date);
            window.history.replaceState({}, '', url);
        }
    } catch (error) {
        console.error('Error loading schedule:', error);
        document.getElementById('filterForm').submit();
    } finally {
        loading.classList.add('hidden');
    }
}

// ✅ Fungsi: Update UI dengan data jadwal baru dari AJAX
function updateScheduleUI(scheduleData, dayName, date) {
    document.querySelectorAll('[data-lab] span.bg-blue-600').forEach(el => {
        el.textContent = dayName;
    });

    const infoText = document.querySelector('.text-gray-500 strong.text-blue-600:last-child');
    if (infoText) {
        const labName = document.querySelector('select[name="lab"]')?.value || 'Semua Lab';
        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric'
        });
        infoText.textContent = `${dayName}, ${formattedDate}`;
    }

    Object.keys(scheduleData).forEach(labName => {
        const tbody = document.getElementById(`scheduleBody-${labName.replace(/\s+/g, '-').toLowerCase()}`);
        if (!tbody) return;

        const sessions = scheduleData[labName];
        let html = '';

        sessions.forEach((item, index) => {
            if (item.is_break) {
                html += `<tr class="bg-gray-100"><td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td><td class="px-4 py-3 text-sm font-medium text-gray-900">${item.session}</td><td class="px-4 py-3 text-sm text-gray-600 font-mono">${item.start} - ${item.end}</td><td class="px-4 py-3"><span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-300 text-gray-700">Istirahat</span></td><td class="px-4 py-3 text-xs text-gray-500">-</td></tr>`;
            } else {
                const colorClass = item.status_color === 'green' ? 'bg-green-100 text-green-800 border-green-300' :
                                  item.status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' :
                                  item.status_color === 'red' ? 'bg-red-100 text-red-800 border-red-300' :
                                  item.status_color === 'orange' ? 'bg-orange-100 text-orange-800 border-orange-300' :
                                  item.status_color === 'blue' ? 'bg-blue-100 text-blue-800 border-blue-300' :
                                  item.status_color === 'indigo' ? 'bg-indigo-100 text-indigo-800 border-indigo-300' :
                                  'bg-gray-400 text-gray-100';

                const bookingInfo = item.booking_info ? ` - ${item.booking_info}` : '';
                let actionBtn = '';

                if (item.status === 'tersedia') {
                    actionBtn = `<button onclick="openBookingModal('${labName}', '${item.session}', '${item.start}', '${item.end}', '${date}')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium">📅 Booking</button>`;
                } else if (item.status === 'approved_teknisi') {
                    actionBtn = `<form action="/booking/${item.booking_id}/approve-kalab" method="POST">@csrf<button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-medium">👔 Approve</button></form>`;
                } else if (item.status === 'proses') {
                    actionBtn = `<span class="text-xs text-yellow-600">⏳ Proses</span>`;
                } else if (item.status === 'terisi') {
                    actionBtn = `<span class="text-xs text-red-600">❌ Terisi</span>`;
                } else {
                    actionBtn = `<span class="text-xs text-gray-500">Selesai</span>`;
                }

                html += `<tr class="hover:bg-gray-50"><td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td><td class="px-4 py-3 text-sm font-medium text-gray-900">${item.session}</td><td class="px-4 py-3 text-sm text-gray-600 font-mono">${item.start} - ${item.end}</td><td class="px-4 py-3"><span class="px-3 py-1 text-xs font-semibold rounded-full cursor-pointer ${colorClass}" onclick="showStatusInfo('${labName}', '${item.session}', '${item.status_label}', '${item.status_color}', '${item.booking_info?.replace(/'/g, "\\'") || ''}')">${item.status_label}</span></td><td class="px-4 py-3 text-sm">${actionBtn}</td></tr>`;
            }
        });

        tbody.innerHTML = html;
    });
}

// Status modal functions
function showStatusInfo(lab, session, status, color, bookingInfo = '') {
    document.getElementById('modalLab').textContent = lab;
    document.getElementById('modalSession').textContent = session;
    document.getElementById('modalStatus').textContent = status;

    const infoEl = document.getElementById('modalInfo');
    const messageEl = document.getElementById('modalMessage');
    const statusEl = document.getElementById('modalStatus');

    if (bookingInfo && bookingInfo.trim() !== '') {
        infoEl.textContent = '📋 ' + bookingInfo;
        infoEl.classList.remove('hidden');
    } else {
        infoEl.classList.add('hidden');
    }

    let message = '';
    if (color === 'green') { message = '✅ Laboratorium tersedia untuk booking.'; statusEl.className = 'font-semibold text-green-600'; }
    else if (color === 'yellow') { message = '⏳ Sesi ini sedang berlangsung.'; statusEl.className = 'font-semibold text-yellow-600'; }
    else if (color === 'red') { message = '❌ Laboratorium sudah terisi pada sesi ini.'; statusEl.className = 'font-semibold text-red-600'; }
    else if (color === 'orange') { message = '⏳ Menunggu approval dosen.'; statusEl.className = 'font-semibold text-orange-600'; }
    else if (color === 'blue') { message = '✅ Disetujui dosen, menunggu teknisi.'; statusEl.className = 'font-semibold text-blue-600'; }
    else if (color === 'indigo') { message = '✅ Disetujui teknisi, menunggu approval Kalab.'; statusEl.className = 'font-semibold text-indigo-600'; }
    else { message = '⏹️ Sesi ini sudah selesai.'; statusEl.className = 'font-semibold text-gray-600'; }

    messageEl.textContent = message;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() { document.getElementById('statusModal').classList.add('hidden'); }
document.getElementById('statusModal')?.addEventListener('click', function(e) { if (e.target === this) closeStatusModal(); });

// Booking modal functions
function openBookingModal(lab, session, start, end, date) {
    document.getElementById('bookingLab').value = lab;
    document.getElementById('bookingSession').value = session;
    document.getElementById('bookingStartTime').value = start;
    document.getElementById('bookingEndTime').value = end;
    document.getElementById('formLab').textContent = lab;
    document.getElementById('formSession').textContent = session;
    document.getElementById('formTime').textContent = start + ' - ' + end;
    document.getElementById('bookingDate').value = date || new Date().toISOString().split('T')[0];
    document.getElementById('bookingModal').classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('bookingForm').reset();
}

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
            showToast('✅ ' + result.message, 'success');
            closeBookingModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('❌ ' + (result.message || 'Terjadi kesalahan'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('❌ Terjadi kesalahan saat melakukan booking', 'error');
    }
}

document.getElementById('bookingModal')?.addEventListener('click', function(e) { if (e.target === this) closeBookingModal(); });

// Simple toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white text-sm z-50 animate-fade-in ${
        type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
        closeBookingModal();
    }
});
</script>
@endpush

@endsection
