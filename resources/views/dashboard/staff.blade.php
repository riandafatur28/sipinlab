@extends('layouts.app')

@php
use Carbon\Carbon;
@endphp

@section('title', 'Dashboard Staff - Polije')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- ======================================================================== -->
    <!-- ✅ REAL-TIME CLOCK HEADER -->
    <!-- ======================================================================== -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-medium text-blue-100 mb-1">🕐 Waktu Sekarang</h2>
                <div class="text-3xl md:text-4xl font-bold" id="realtime-clock">00:00:00</div>
                <div class="text-blue-200 mt-1">
                    {{ $realtimeDayName ?? Carbon::now()->isoFormat('dddd') }}, {{ ($currentTime ?? Carbon::now())->isoFormat('D MMMM Y') }}
                </div>
            </div>
            <div class="hidden md:block">
                <svg class="w-20 h-20 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ STATS CARDS: Berdasarkan Role (Kalab / Teknisi / Dosen) -->
    <!-- ======================================================================== -->
    @php
        $isKalab = Auth::user()->isKalab() || Auth::user()->role === 'ketua_lab';
        $isTeknisi = Auth::user()->isTeknisi();
        $userLab = Auth::user()->lab_name ?? '';
    @endphp

    @if($isKalab || $isTeknisi)
    <div class="mb-6">
        <!-- Header Stats -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">
                {{ $isKalab ? '📊 Statistik Semua Laboratorium' : '📊 Statistik Laboratorium: ' . $userLab }}
            </h3>
            @if($isTeknisi)
                <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    🔒 Hanya data lab {{ $userLab }}
                </span>
            @endif
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">

            <!-- 🏢 Total Labs -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs text-gray-500">🏢 Total Lab</p>
                <p class="text-lg font-bold text-indigo-600">{{ $stats['total_labs'] ?? 0 }}</p>
            </div>

            <!-- 📚 Active Courses -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs text-gray-500">📚 Mata Kuliah</p>
                <p class="text-lg font-bold text-teal-600">{{ $stats['active_courses'] ?? 0 }}</p>
            </div>

            <!-- 📅 Booking Hari Ini -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-green-500 opacity-20"></div>
                <p class="text-xs text-gray-500">📅 Hari Ini</p>
                <p class="text-lg font-bold text-green-600">{{ $stats['bookings_today'] ?? 0 }}</p>
            </div>

            <!-- 📆 Booking Minggu Ini -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 opacity-20"></div>
                <p class="text-xs text-gray-500">📆 Minggu Ini</p>
                <p class="text-lg font-bold text-blue-600">{{ $stats['bookings_this_week'] ?? 0 }}</p>
            </div>

            <!-- 📊 Booking Bulan Ini -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-purple-500 opacity-20"></div>
                <p class="text-xs text-gray-500">📊 Bulan Ini</p>
                <p class="text-lg font-bold text-purple-600">{{ $stats['bookings_current_month'] ?? 0 }}</p>
            </div>

            <!-- 📈 Booking Bulan Lalu -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-orange-500 opacity-20"></div>
                <p class="text-xs text-gray-500">📈 Bulan Lalu</p>
                <p class="text-lg font-bold text-orange-600">{{ $stats['bookings_last_month'] ?? 0 }}</p>
            </div>

            <!-- ⏳ Pending -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs text-gray-500">⏳ Pending</p>
                <p class="text-lg font-bold text-yellow-600">{{ $stats['pending_count'] ?? 0 }}</p>
            </div>

            <!-- ✅ Confirmed -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs text-gray-500">✅ Confirmed</p>
                <p class="text-lg font-bold text-green-600">{{ $stats['confirmed_count'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats untuk Dosen/Staff biasa (minimal) -->
    @if(!$isKalab && !$isTeknisi && !empty($stats['my_bookings']))
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Booking Saya</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['my_bookings'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Menunggu</p>
            <p class="text-xl font-bold text-yellow-600">{{ $stats['my_pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Disetujui</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['my_confirmed'] }}</p>
        </div>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ SECTION GRAFIK ANALYTICS (4 Charts) - Hanya untuk Kalab/Teknisi -->
    <!-- ======================================================================== -->
    @if($isKalab || $isTeknisi)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- 📊 Chart 1: Lab Paling Sering Dipinjam -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">🏢</span>
                Lab Paling Sering Dipinjam
                <span class="text-xs text-gray-500 font-normal ml-2">(30 hari terakhir)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartLabUsage"></canvas>
            </div>
        </div>

        <!-- 📊 Chart 2: Hari Paling Banyak Dipilih -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">📅</span>
                Hari Paling Banyak Dipilih
                <span class="text-xs text-gray-500 font-normal ml-2">(Semua booking)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartDayDistribution"></canvas>
            </div>
        </div>

        <!-- 📊 Chart 3: Jenis Kegiatan Peminjaman -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">📋</span>
                Jenis Kegiatan Peminjaman
                <span class="text-xs text-gray-500 font-normal ml-2">(3 bulan terakhir)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartActivityType"></canvas>
            </div>
        </div>

        <!-- 📊 Chart 4: Top Peminjam -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">👤</span>
                Top Peminjam
                <span class="text-xs text-gray-500 font-normal ml-2">(3 bulan terakhir)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartTopBorrowers"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ FILTER: Calendar + Day + Lab + Kalab View Mode Toggle -->
    <!-- ======================================================================== -->
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
                        <option value="{{ $day }}" {{ ($scheduleDayName ?? '') == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Lab Selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">🏢 Lab:</label>
                <select name="lab" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                    <option value="">Semua Lab</option>
                    @foreach(($labs ?? []) as $labName)
                        <option value="{{ $labName }}" {{ request('lab') == $labName ? 'selected' : '' }}>
                            {{ $labName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Info Text -->
            <span class="text-xs text-gray-500 ml-2">
                Menampilkan: <strong class="text-blue-600">{{ request('lab') ?: 'Semua Lab' }}</strong>
                pada <strong class="text-blue-600">{{ $scheduleDayName ?? '' }}, {{ Carbon::parse($scheduleDate ?? date('Y-m-d'))->isoFormat('D MMM Y') }}</strong>
                @if(Auth::user()->isKalab())
                    | Mode: <strong class="{{ session('dashboard_view_mode', 'schedule') === 'management' ? 'text-indigo-600' : 'text-blue-600' }}">
                        {{ session('dashboard_view_mode', 'schedule') === 'management' ? '👔 Kalab' : '🎓 Dosen' }}
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

    <!-- ======================================================================== -->
    <!-- ✅ Stats Dashboard untuk Kalab View (Approval Stats) -->
    <!-- ======================================================================== -->
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

    <!-- ======================================================================== -->
    <!-- ✅ LEGEND / KETERANGAN STATUS -->
    <!-- ======================================================================== -->
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

    <!-- ======================================================================== -->
    <!-- ✅ LOADING INDICATOR (untuk AJAX calendar) -->
    <!-- ======================================================================== -->
    <div id="loadingIndicator" class="hidden mb-6 text-center py-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memuat jadwal...</span>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ TABLES PER LAB (Schedule Grid) - AUTO-LOAD HARI INI -->
    <!-- ======================================================================== -->
    @php
        $displayLabs = request('lab') ? [request('lab')] : ($labs ?? []);
    @endphp

    <div id="scheduleContainer">
        @foreach($displayLabs as $lab)
            @if(in_array($lab, ($labs ?? [])))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6" data-lab="{{ $lab }}">
                <!-- Header Lab -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">{{ $lab }}</h2>
                    <span class="text-lg font-extrabold text-white bg-blue-600 px-4 py-1.5 rounded-lg shadow-sm">
                        {{ $scheduleDayName ?? Carbon::now()->isoFormat('dddd') }}
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
                            @foreach(($scheduleData[$lab] ?? []) as $item)
                            <tr class="hover:bg-gray-50 transition-colors {{ ($item['is_break'] ?? false) ? 'bg-gray-100' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item['no'] ?? $loop->iteration }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['session'] ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-mono">
                                    {{ $item['start'] ?? '-' }} - {{ $item['end'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if(($item['is_break'] ?? false))
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-300 text-gray-700">Istirahat</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full cursor-pointer transition-transform hover:scale-105
                                            @if(($item['status_color'] ?? '') === 'green') bg-green-100 text-green-800 border border-green-300
                                            @elseif(($item['status_color'] ?? '') === 'yellow') bg-yellow-100 text-yellow-800 border border-yellow-300
                                            @elseif(($item['status_color'] ?? '') === 'red') bg-red-100 text-red-800 border border-red-300
                                            @elseif(($item['status_color'] ?? '') === 'orange') bg-orange-100 text-orange-800 border border-orange-300
                                            @elseif(($item['status_color'] ?? '') === 'blue') bg-blue-100 text-blue-800 border border-blue-300
                                            @elseif(($item['status_color'] ?? '') === 'indigo') bg-indigo-100 text-indigo-800 border border-indigo-300
                                            @else bg-gray-400 text-gray-100 @endif"
                                            onclick="showStatusInfo('{{ $lab }}', '{{ $item['session'] ?? '' }}', '{{ $item['status_label'] ?? '' }}', '{{ $item['status_color'] ?? '' }}', '{{ addslashes($item['booking_info'] ?? '') }}')">
                                            {{ $item['status_label'] ?? '-' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if(!($item['is_break'] ?? false))
                                        @if(($item['status'] ?? '') === 'tersedia')
                                            <button onclick="openBookingModal('{{ $lab }}', '{{ $item['session'] ?? '' }}', '{{ $item['start'] ?? '' }}', '{{ $item['end'] ?? '' }}', '{{ $scheduleDate ?? date('Y-m-d') }}')"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                                📅 Booking
                                            </button>
                                        @elseif(in_array(($item['status'] ?? ''), ['pending', 'approved_dosen', 'approved_teknisi']) && $isKalabView)
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
                                        @elseif(($item['status'] ?? '') === 'proses')
                                            <span class="text-xs text-yellow-600 font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"></path>
                                                </svg>
                                                Proses
                                            </span>
                                        @elseif(($item['status'] ?? '') === 'terisi')
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

        @if(empty($displayLabs) || (request('lab') && !in_array(request('lab'), ($labs ?? []))))
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
            <p class="text-gray-500">Laboratorium tidak ditemukan.</p>
            <a href="{{ route('dashboard.staff') }}" class="text-blue-600 hover:underline mt-2 inline-block">Reset filter</a>
        </div>
        @endif
    </div>

</div>

<!-- ======================================================================== -->
<!-- ✅ MODALS (Status & Booking) -->
<!-- ======================================================================== -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">📋 Informasi Status</h3>
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

<!-- ======================================================================== -->
<!-- ✅ JAVASCRIPT: Charts + Real-time Updates + Auto-load Schedule -->
<!-- ======================================================================== -->
@push('scripts')
<!-- ✅ Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>

<script>
// ========================================================================
// 🎨 COLOR PALETTE FOR CHARTS
// ========================================================================
const colors = {
    blue: '#3b82f6', indigo: '#6366f1', purple: '#8b5cf6',
    green: '#22c55e', yellow: '#eab308', orange: '#f97316',
    red: '#ef4444', gray: '#6b7280', teal: '#14b8a6'
};

// ========================================================================
// 📊 CHART 1: Lab Usage (Bar Chart)
// ========================================================================
const ctxLab = document.getElementById('chartLabUsage');
if (ctxLab && @json($chartLabLabels ?? []).length > 0) {
    new Chart(ctxLab, {
        type: 'bar',
        data: {
            labels: @json($chartLabLabels ?? []),
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: @json($chartLabData ?? []),
                backgroundColor: [colors.blue, colors.indigo, colors.purple, colors.green, colors.orange],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => `${ctx.parsed} booking` } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
}

// ========================================================================
// 📊 CHART 2: Day Distribution (Pie Chart)
// ========================================================================
const ctxDay = document.getElementById('chartDayDistribution');
if (ctxDay && @json($chartDayLabels ?? []).length > 0) {
    new Chart(ctxDay, {
        type: 'pie',
        data: {
            labels: @json($chartDayLabels ?? []),
            datasets: [{
                data: @json($chartDayData ?? []),
                backgroundColor: [colors.blue, colors.green, colors.yellow, colors.orange, colors.red, colors.purple, colors.indigo],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} booking` } }
            }
        }
    });
}

// ========================================================================
// 📊 CHART 3: Activity Type (Doughnut Chart)
// ========================================================================
const ctxActivity = document.getElementById('chartActivityType');
if (ctxActivity && @json($chartActivityLabels ?? []).length > 0) {
    new Chart(ctxActivity, {
        type: 'doughnut',
        data: {
            labels: @json($chartActivityLabels ?? []),
            datasets: [{
                data: @json($chartActivityData ?? []),
                backgroundColor: [colors.blue, colors.green, colors.purple, colors.orange, colors.indigo, colors.yellow],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} booking` } }
            }
        }
    });
}

// ========================================================================
// 📊 CHART 4: Top Borrowers (Horizontal Bar)
// ========================================================================
const ctxBorrowers = document.getElementById('chartTopBorrowers');
if (ctxBorrowers && @json($chartBorrowerLabels ?? []).length > 0) {
    new Chart(ctxBorrowers, {
        type: 'bar',
        data: {
            labels: @json($chartBorrowerLabels ?? []),
            datasets: [{
                label: 'Jumlah Booking',
                data: @json($chartBorrowerData ?? []),
                backgroundColor: @json($chartBorrowerRoles ?? []).map(role =>
                    role === 'mahasiswa' ? colors.blue :
                    role === 'dosen' ? colors.green :
                    role === 'ketua_lab' ? colors.purple :
                    role === 'teknisi' ? colors.orange : colors.gray
                ),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });
}

// ========================================================================
// ⏰ REAL-TIME CLOCK
// ========================================================================
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);
updateClock();

// ========================================================================
// 🗓️ DAY MAPPING FOR JAVASCRIPT
// ========================================================================
const jsDayIndexMap = {
    'Minggu': 0, 'Senin': 1, 'Selasa': 2, 'Rabu': 3,
    'Kamis': 4, 'Jumat': 5, 'Sabtu': 6
};
const jSDayNamesArray = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// ========================================================================
// 🔄 DATE/DAY SYNC FUNCTIONS
// ========================================================================
function onDateChange(dateValue) {
    if (!dateValue) return;
    const date = new Date(dateValue + 'T00:00:00');
    const jsIndex = date.getDay();
    const dayName = jSDayNamesArray[jsIndex];
    document.getElementById('daySelect').value = dayName;
    document.getElementById('filterForm').submit();
}

function onDayChange(dayName) {
    const today = new Date();
    const targetDayNum = jsDayIndexMap[dayName];
    if (targetDayNum === undefined) return;
    const todayDayNum = today.getDay();
    let diff = targetDayNum - todayDayNum;
    if (diff < 0) diff += 7;
    const targetDate = new Date(today);
    targetDate.setDate(today.getDate() + diff);
    const yyyy = targetDate.getFullYear();
    const mm = String(targetDate.getMonth() + 1).padStart(2, '0');
    const dd = String(targetDate.getDate()).padStart(2, '0');
    document.getElementById('datePicker').value = `${yyyy}-${mm}-${dd}`;
    document.getElementById('filterForm').submit();
}

// ========================================================================
// 👁️ TOGGLE VIEW MODE FOR KALAB (AJAX)
// ========================================================================
function toggleViewMode(mode) {
    // Optimistic UI update
    document.querySelectorAll('[onclick*="toggleViewMode"]').forEach(btn => {
        btn.classList.remove('bg-white', 'text-blue-600', 'text-indigo-600', 'shadow');
        btn.classList.add('text-gray-500');
    });
    event.currentTarget.classList.remove('text-gray-500');
    event.currentTarget.classList.add('bg-white', mode === 'management' ? 'text-indigo-600' : 'text-blue-600', 'shadow');

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

// ========================================================================
// 💬 MODAL FUNCTIONS
// ========================================================================
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

// ========================================================================
// 📤 BOOKING SUBMISSION (AJAX)
// ========================================================================
async function submitBooking(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
        const response = await fetch('{{ route("booking.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const result = await response.json();
        if (result.success) {
            showToast('✅ ' + result.message, 'success');
            closeBookingModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('❌ ' + (result.message || 'Terjadi kesalahan'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('❌ Terjadi kesalahan saat melakukan booking', 'error');
    }
}

// ========================================================================
// 🔔 TOAST NOTIFICATION
// ========================================================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white text-sm z-50 ${
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

// ========================================================================
// 🎯 EVENT LISTENERS
// ========================================================================
document.getElementById('statusModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
document.getElementById('bookingModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBookingModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
        closeBookingModal();
    }
});

// ========================================================================
// ✅ AUTO-LOAD: Jadwal hari ini langsung muncul tanpa user pilih tanggal
// ========================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Jika tidak ada parameter date, set default ke hari ini
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('date')) {
        urlParams.set('date', new Date().toISOString().split('T')[0]);
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.replaceState({}, document.title, newUrl);
    }

    // Refresh jika ada parameter refresh
    if (urlParams.has('refresh')) {
        urlParams.delete('refresh');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, newUrl);
        setTimeout(() => window.location.reload(), 500);
    }
});
</script>
@endpush

@endsection
