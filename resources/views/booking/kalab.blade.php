@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab - Konfirmasi Final')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">👔 Dashboard Ketua Laboratorium</h1>
            <p class="text-gray-600 mt-1">Kelola konfirmasi final peminjaman laboratorium</p>
        </div>

        @if(Auth::user()->isKalab() || Auth::user()->role === 'ketua_lab')
            <a href="{{ route('admin.labs.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Lab Baru
            </a>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Awaiting Final -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
            <div class="absolute right-0 top-0 h-full w-1 bg-orange-500 opacity-20"></div>
            <p class="text-sm text-gray-600 mb-1">⏳ Menunggu Konfirmasi Final</p>
            <p class="text-3xl font-bold text-orange-600">{{ $stats['awaiting_final'] ?? 0 }}</p>
        </div>

        <!-- Confirmed Today -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
            <div class="absolute right-0 top-0 h-full w-1 bg-green-500 opacity-20"></div>
            <p class="text-sm text-gray-600 mb-1">✅ Dikonfirmasi Hari Ini</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['confirmed_today'] ?? 0 }}</p>
        </div>

        <!-- Total Confirmed -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
            <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 opacity-20"></div>
            <p class="text-sm text-gray-600 mb-1">📊 Total Dikonfirmasi</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_confirmed'] ?? 0 }}</p>
        </div>

        <!-- Ready to Print -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
            <div class="absolute right-0 top-0 h-full w-1 bg-purple-500 opacity-20"></div>
            <p class="text-sm text-gray-600 mb-1">🖨️ Siap Cetak</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['total_confirmed'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('success') }}
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

    <!-- ======================================================================== -->
    <!-- ✅ SECTION 1: BOOKING YANG PERLU KONFIRMASI KA LAB -->
    <!-- ======================================================================== -->
    @if($pendingApprovals->count() > 0)
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
                            <p class="font-semibold">{{ $booking->booking_date->format('d M Y') }}</p>
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
                            <a href="{{ route('booking.show', $booking->id) }}"
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
    @else
    <!-- Empty State -->
    <div class="mb-10 bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Tidak Ada Booking yang Menunggu</h3>
        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Semua booking yang masuk sudah ditindaklanjuti atau diproses oleh tim terkait.</p>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ SECTION 2: SEMUA BOOKING YANG SUDAH DIKONFIRMASI -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">✅ Booking yang Sudah Dikonfirmasi</h2>
                <p class="text-sm text-gray-500 mt-1">Data historis peminjaman yang telah diselesaikan</p>
            </div>

            <!-- Optional: Filter Search untuk Tabel ini bisa dikembangkan -->
             <div class="flex items-center gap-2">
                 <input type="text" placeholder="Cari nama..." class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                 <button class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                 </button>
             </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dikonfirmasi Oleh</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Manajemen Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($confirmedBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 text-xs">
                                    {{ substr($booking->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->user->role }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{{ $booking->lab_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMM YYYY') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->session }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                             {{ $booking->approved_at_kalab ? \Carbon\Carbon::parse($booking->approved_at_kalab)->isoFormat('D MMM HH:mm') : '-' }}
                             @if($booking->approved_by_kalab)
                             <span class="block text-xs text-gray-400">Oleh: {{ $booking->userById($booking->approved_by_kalab)?->name ?? 'Admin' }}</span>
                             @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-y-1">
                             {{-- Tombol Detail --}}
                            <a href="{{ route('booking.show', $booking->id) }}"
                               title="Lihat Detail"
                               class="text-blue-600 hover:text-blue-800 transition-colors inline-block p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>

                            {{-- Tombol Cetak PDF --}}
                             @if(Auth::check() && (Auth::user()->isAdmin() || Auth::user()->isKalab() || Auth::user()->role === 'ketua_lab'))
                             <a href="{{ route('booking.download-approved', $booking) }}"
                                target="_blank"
                                class="text-purple-600 hover:text-purple-800 transition-colors inline-block p-1"
                                title="Download Form PDF Resmi">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                             @else
                             <a href="#" class="text-gray-400 cursor-not-allowed" title="Anda tidak memiliki akses ini">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                             @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 bg-gray-50">
                            Belum ada booking yang dikonfirmasi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($confirmedBookings->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $confirmedBookings->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
