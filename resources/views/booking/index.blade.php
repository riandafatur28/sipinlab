@extends('layouts.app')

@section('title', 'Booking Laboratorium - SiPinLab')

@section('content')
@php
    $user = Auth::user();
    $isMahasiswa = $user->isMahasiswa();
    $isDosen = $user->isDosen();
    $isKalab = $user->isKalab();
    $isStaff = !$isMahasiswa && !$isDosen && !$isKalab; // Admin, Teknisi, Staff
    $isDosenAndKalab = $isDosen && $isKalab;

    // Tab navigation untuk dual role
    $currentTab = request('tab', $isDosenAndKalab ? 'mybookings' : null);
@endphp

<div class="max-w-7xl mx-auto">

    <!-- ======================================================================== -->
    <!-- HEADER + ACTION BUTTON -->
    <!-- ======================================================================== -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                @if($isMahasiswa)
                    📋 Booking Saya
                @elseif($isDosenAndKalab)
                    👔 Dashboard Ketua Lab & Dosen
                @elseif($isDosen)
                    📋 Booking & Persetujuan Saya
                @elseif($isKalab)
                    👔 Dashboard Ketua Laboratorium
                @else
                    📊 Kelola Booking
                @endif
            </h1>
            <p class="text-gray-600">
                @if($isMahasiswa)
                    Daftar peminjaman laboratorium Anda
                @elseif($isDosenAndKalab)
                    Kelola booking pribadi dan konfirmasi final mahasiswa
                @elseif($isDosen)
                    Daftar booking dan persetujuan mahasiswa
                @elseif($isKalab)
                    Kelola konfirmasi final peminjaman laboratorium
                @else
                    Dashboard manajemen peminjaman laboratorium
                @endif
            </p>
        </div>

        <!-- ✅ TOMBOL BOOKING BARU (Hanya untuk Mahasiswa & Dosen) -->
        @if($isMahasiswa || $isDosen)
        <a href="{{ $isMahasiswa ? route('booking.create') : route('booking.create-dosen') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            + Buat Peminjaman Baru
        </a>
        @endif
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3 animate-pulse">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ session('success') }}
        <button onclick="this.closest('.animate-pulse')?.remove()" class="ml-auto text-green-800 hover:text-green-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    @endif

    @if(session('info'))
    <div class="mb-6 p-4 bg-blue-50 border border-blue-300 rounded-lg text-blue-800 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ session('info') }}
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

    <!-- ======================================================================== -->
    <!-- ✅ TAB NAVIGATION (Khusus Dosen yang juga Kalab) -->
    <!-- ======================================================================== -->
    @if($isDosenAndKalab)
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8 overflow-x-auto" role="tablist">
            <a href="{{ route('booking.index', array_merge(request()->except('tab'), ['tab' => 'mybookings'])) }}"
               role="tab"
               class="{{ $currentTab === 'mybookings' || !$currentTab
                   ? 'border-blue-500 text-blue-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                📋 Booking Saya
            </a>
            <a href="{{ route('booking.index', array_merge(request()->except('tab'), ['tab' => 'approvals'])) }}"
               role="tab"
               class="{{ $currentTab === 'approvals'
                   ? 'border-blue-500 text-blue-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                ⏳ Persetujuan Mahasiswa
                @php
                    $pendingCount = \App\Models\Booking::where('status', 'pending')
                        ->whereHas('user', fn($q) => $q->where('role', 'mahasiswa'))
                        ->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('booking.index', array_merge(request()->except('tab'), ['tab' => 'management'])) }}"
               role="tab"
               class="{{ $currentTab === 'management'
                   ? 'border-blue-500 text-blue-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                👔 Management Kalab
            </a>
        </nav>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ STATS CARDS (Role-Based) -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @if($isMahasiswa || $isDosen)
            <!-- Stats untuk Mahasiswa/Dosen -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Total Booking</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Menunggu</p>
                <p class="text-xl md:text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Disetujui</p>
                <p class="text-xl md:text-2xl font-bold text-green-600">{{ $stats['confirmed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Ditolak</p>
                <p class="text-xl md:text-2xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</p>
            </div>

        @elseif($isKalab || $isDosenAndKalab && $currentTab === 'management')
            <!-- Stats untuk Kalab / Management Mode -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-orange-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">⏳ Menunggu Final</p>
                <p class="text-xl md:text-2xl font-bold text-orange-600">{{ $stats['awaiting_final'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-green-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">✅ Hari Ini</p>
                <p class="text-xl md:text-2xl font-bold text-green-600">{{ $stats['confirmed_today'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">📊 Total Dikonfirmasi</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600">{{ $stats['total_confirmed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-purple-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">🖨️ Siap Cetak</p>
                <p class="text-xl md:text-2xl font-bold text-purple-600">{{ $stats['total_confirmed'] ?? 0 }}</p>
            </div>

        @else
            <!-- Stats untuk Staff/Admin/Teknisi -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Total Booking</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600">{{ $stats['total_booking'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Menunggu</p>
                <p class="text-xl md:text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Dikonfirmasi</p>
                <p class="text-xl md:text-2xl font-bold text-green-600">{{ $stats['confirmed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Hari Ini</p>
                <p class="text-xl md:text-2xl font-bold text-purple-600">{{ $stats['hari_ini'] ?? 0 }}</p>
            </div>
        @endif
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ SEARCH & FILTER FORM (Hanya untuk Staff/Kalab Management) -->
    <!-- ======================================================================== -->
    @if($isStaff || ($isKalab && (!$isDosen || $currentTab === 'management')) || ($isDosenAndKalab && $currentTab === 'management'))
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
        <form action="{{ route('booking.index') }}" method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

            <!-- 🔍 Search Input -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Cari (Nama/NIM/NIP)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, NIM, atau NIP..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                       onkeyup="if(event.key==='Enter') document.getElementById('filterForm').submit()">
            </div>

            <!-- 🏢 Lab Dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="lab" onchange="document.getElementById('filterForm').submit()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                    <option value="">Semua Lab</option>
                    @foreach($labs as $lab)
                        <option value="{{ $lab }}" {{ request('lab') == $lab ? 'selected' : '' }}>{{ $lab }}</option>
                    @endforeach
                </select>
            </div>

            <!-- ⚠️ Status Dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" onchange="document.getElementById('filterForm').submit()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Menunggu</option>
                    <option value="approved_dosen" {{ request('status') == 'approved_dosen' ? 'selected' : '' }}>✅ Dosen</option>
                    <option value="approved_teknisi" {{ request('status') == 'approved_teknisi' ? 'selected' : '' }}>✅ Teknisi</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>✅ Dikonfirmasi</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>🗑️ Dibatalkan</option>
                </select>
            </div>

            <!-- 🔘 Submit Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
            </div>
        </form>

        @if(request()->hasAny(['search', 'lab', 'status', 'date_start', 'date_end']))
        <div class="mt-3 text-right">
            <a href="{{ route('booking.index', array_merge(request()->all(), ['tab' => $currentTab])) }}" class="text-xs text-blue-600 hover:underline">
                🔄 Reset filter
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ SECTION KHUSUS DOSEN: Persetujuan Booking Mahasiswa -->
    <!-- ======================================================================== -->
    @if($isDosen && (!$isKalab || $currentTab === 'approvals' || $currentTab === 'mybookings'))
    @php
        // Ambil booking yang menunggu persetujuan dosen (hanya jika di tab approvals atau default)
        $showApprovalSection = $currentTab === 'approvals' || (!$currentTab && !$isKalab);
        $pendingBookingsForDosen = $showApprovalSection
            ? \App\Models\Booking::where('status', 'pending')
                ->whereHas('user', function($q) { $q->where('role', 'mahasiswa'); })
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();
    @endphp

    @if($showApprovalSection && $pendingBookingsForDosen->count() > 0)
    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-4 flex items-center gap-2">
            ⏳ Persetujuan Booking Mahasiswa ({{ $pendingBookingsForDosen->count() }})
        </h3>

        <div class="space-y-3">
            @foreach($pendingBookingsForDosen as $booking)
            <div class="bg-white rounded-lg p-4 border border-yellow-200 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-semibold text-gray-900">{{ $booking->user->name }}</span>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                {{ $booking->user->nim ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Lab:</strong> {{ $booking->lab_name }}</p>
                            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }} | <strong>Sesi:</strong> {{ $booking->session }}</p>
                            <p><strong>Keperluan:</strong> {{ Str::limit($booking->purpose, 100) }}</p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <!-- Tombol Approve -->
                        <form action="{{ route('booking.approve-dosen', $booking) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('✅ Setujui booking dari {{ $booking->user->name }}?')"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Setujui
                            </button>
                        </form>

                        <!-- Tombol Reject -->
                        <button type="button"
                                onclick="showRejectModal({{ $booking->id }}, '{{ $booking->user->name }}')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ SECTION KHUSUS KALAB: Booking yang Menunggu Konfirmasi Final -->
    <!-- ======================================================================== -->
    @if(($isKalab || ($isDosenAndKalab && $currentTab === 'management')) && isset($pendingApprovals) && $pendingApprovals->count() > 0)
    <div class="mb-10 bg-white rounded-xl shadow-lg border border-purple-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-purple-100 bg-gradient-to-r from-purple-50 to-white">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-purple-900">📋 Menunggu Konfirmasi Final (Ka Lab)</h2>
                    <p class="text-sm text-purple-600 mt-1 ml-1">Ada <strong>{{ $pendingApprovals->total() }}</strong> booking menunggu persetujuan akhir.</p>
                </div>
                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold border border-purple-200">
                    Prioritas Tinggi
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($pendingApprovals as $booking)
                    <tr class="hover:bg-purple-50/50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center font-bold text-purple-700">
                                    {{ substr($booking->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $booking->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($booking->user->role) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                            {{ $booking->lab_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->session }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <p class="font-medium text-gray-700">{{ $booking->activity }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-[150px]">{{ Str::limit($booking->purpose, 40) }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                Pending Approval
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('booking.show', $booking) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Konfirmasi
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($pendingApprovals->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $pendingApprovals->links() }}
        </div>
        @endif
    </div>
    @elseif(($isKalab || ($isDosenAndKalab && $currentTab === 'management')) && isset($pendingApprovals) && $pendingApprovals->count() === 0)
    <!-- Empty State untuk Kalab -->
    <div class="mb-10 bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Tidak Ada Booking yang Menunggu</h3>
        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Semua booking yang masuk sudah ditindaklanjuti atau diproses oleh tim terkait.</p>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ BOOKING LIST TABLE (Utama untuk semua role) -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center flex-wrap gap-2">
            <h2 class="text-lg md:text-xl font-bold text-gray-800">
                @if($isMahasiswa)
                    📋 Riwayat Booking Saya
                @elseif($isDosen && !$isKalab)
                    📊 Semua Booking Saya
                @elseif($isDosenAndKalab && $currentTab === 'approvals')
                    ⏳ Persetujuan Mahasiswa
                @elseif($isDosenAndKalab && $currentTab === 'management')
                    📊 Management Booking
                @elseif($isKalab)
                    ✅ Booking yang Sudah Dikonfirmasi
                @else
                    📊 Daftar Semua Booking
                @endif
            </h2>

            @if($isMahasiswa || $isDosen)
                <a href="{{ $isMahasiswa ? route('booking.create') : route('booking.create-dosen') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    + Booking Baru
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        @if($isStaff || $isKalab || ($isDosenAndKalab && $currentTab === 'management'))
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Sesi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        @if($isStaff || $isKalab || ($isDosenAndKalab && $currentTab === 'management'))
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $booking->user->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->user->nim ?? $booking->user->nip ?? '-' }}</div>
                            </td>
                        @endif
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $booking->lab_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell">{{ $booking->session }}</td>
                        <td class="px-4 py-3 text-sm">
                            @php
                                $statusClass = match($booking->status) {
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved_dosen' => 'bg-blue-100 text-blue-800',
                                    'approved_teknisi' => 'bg-indigo-100 text-indigo-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusLabel = match($booking->status) {
                                    'confirmed' => '✅ Dikonfirmasi',
                                    'pending' => '⏳ Menunggu Dosen',
                                    'approved_dosen' => '✅ Disetujui Dosen',
                                    'approved_teknisi' => '✅ Disetujui Teknisi',
                                    'rejected' => '❌ Ditolak',
                                    'cancelled' => '🗑️ Dibatalkan',
                                    default => ucfirst(str_replace('_', ' ', $booking->status)),
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('booking.show', $booking) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </a>

                                @if(($isKalab || $isDosenAndKalab) && $booking->status === 'approved_teknisi')
                                    <form action="{{ route('booking.approve-kalab', $booking) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('✅ Konfirmasi final booking ini?')"
                                                class="text-purple-600 hover:text-purple-800 font-medium text-xs">
                                            Konfirmasi
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ ($isStaff || $isKalab || ($isDosenAndKalab && $currentTab === 'management')) ? '6' : '5' }}" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p>Belum ada booking.</p>
                                @if($isMahasiswa || $isDosen)
                                    <a href="{{ $isMahasiswa ? route('booking.create') : route('booking.create-dosen') }}"
                                       class="text-blue-600 hover:underline font-medium">
                                        + Buat booking pertama
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-4 md:px-6 py-4 border-t border-gray-200">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>

</div>

<!-- ================= MODAL REJECT (Shared) ============== -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-gray-200">
        <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">❌ Tolak Booking</h3>
                <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-5">
            <p class="text-gray-700 mb-4">Mohon berikan alasan penolakan agar pemohon dapat memperbaruinya.</p>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <textarea name="rejection_reason" required rows="4" maxlength="500" placeholder="Contoh: Jadwal bentrok dengan kuliah..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Batal</button>
                    <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Submit Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentRejectUrl = '';

function showRejectModal(bookingId, userName) {
    currentRejectUrl = `/booking/${bookingId}/reject`;
    document.getElementById('rejectForm').action = currentRejectUrl;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}

// Close modal jika klik backdrop
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endpush

@endsection
