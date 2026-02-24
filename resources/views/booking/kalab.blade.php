@extends('layouts.app')

@section('title', 'Dashboard Ketua Lab')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">👔 Dashboard Ketua Laboratorium</h1>
        <p class="text-gray-600">Kelola konfirmasi final peminjaman laboratorium</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">⏳ Menunggu Konfirmasi Final</p>
            <p class="text-3xl font-bold text-orange-600">{{ $stats['awaiting_final'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">✅ Dikonfirmasi Hari Ini</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['confirmed_today'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">📊 Total Dikonfirmasi</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_confirmed'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">🖨️ Siap Cetak</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['total_confirmed'] ?? 0 }}</p>
        </div>
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

    <!-- ======================================================================== -->
    <!-- ✅ SECTION 1: BOOKING YANG PERLU KONFIRMASI KA LAB -->
    <!-- ======================================================================== -->
    @if($pendingApprovals->count() > 0)
    <div class="mb-8 bg-purple-50 rounded-xl shadow-sm border border-purple-300 overflow-hidden">
        <div class="px-6 py-4 border-b border-purple-300 bg-purple-100">
            <h2 class="text-xl font-bold text-purple-800">📋 Menunggu Konfirmasi Final (Ka Lab)</h2>
            <p class="text-sm text-purple-600 mt-1">Ada {{ $pendingApprovals->total() }} booking yang perlu dikonfirmasi</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-purple-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Tanggal & Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-purple-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-purple-200">
                    @foreach($pendingApprovals as $booking)
                    <tr class="hover:bg-purple-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-500 text-white flex items-center justify-center font-bold">
                                    {{ substr($booking->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $booking->user->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $booking->user->role === 'mahasiswa' ? 'NIM: ' . ($booking->user->nim ?? 'N/A') : 'NIP: ' . ($booking->user->nip ?? 'N/A') }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $booking->lab_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <p>{{ $booking->booking_date->format('d M Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->session }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <p class="font-medium">{{ $booking->activity }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($booking->purpose, 50) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                                {{ $booking->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('booking.show', $booking->id) }}" 
                               class="inline-flex items-center gap-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Konfirmasi
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($pendingApprovals->hasPages())
        <div class="px-6 py-4 border-t border-purple-200 bg-purple-50">
            {{ $pendingApprovals->links() }}
        </div>
        @endif
    </div>
    @else
    <!-- Empty State -->
    <div class="mb-8 bg-green-50 rounded-xl shadow-sm border border-green-300 p-6 text-center">
        <svg class="w-16 h-16 mx-auto text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-green-800">Tidak Ada Booking yang Menunggu</h3>
        <p class="text-sm text-green-600 mt-1">Semua booking sudah dikonfirmasi</p>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ SECTION 2: SEMUA BOOKING YANG SUDAH DIKONFIRMASI -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">✅ Booking yang Sudah Dikonfirmasi</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Konfirmasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($confirmedBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->user->role }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $booking->lab_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $booking->booking_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $booking->session }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->approved_at_kalab ? $booking->approved_at_kalab->format('d M Y H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('booking.show', $booking->id) }}" 
                               class="text-blue-600 hover:text-blue-800">Detail</a>
                            <br>
                            <a href="{{ route('booking.print', $booking->id) }}" 
                               target="_blank"
                               class="text-green-600 hover:text-green-800 inline-flex items-center gap-1 mt-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Cetak Form
                            </a>
                            <br>
                            <a href="{{ route('booking.download-pdf', $booking->id) }}" 
                               class="text-purple-600 hover:text-purple-800 inline-flex items-center gap-1 mt-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Belum ada booking yang dikonfirmasi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($confirmedBookings->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $confirmedBookings->links() }}
        </div>
        @endif
    </div>

</div>
@endsection