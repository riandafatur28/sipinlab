@extends('layouts.app')

@section('title', 'Booking Saya - Dosen')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">📚 Dashboard Booking - Dosen</h1>
        <p class="text-gray-600">Kelola peminjaman laboratorium Anda dan persetujuan mahasiswa</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Total Booking Saya</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Menunggu Persetujuan</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Disetujui</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">📋 Perlu Approval Anda</p>
            <p class="text-3xl font-bold text-orange-600">{{ $stats['awaiting_approval'] ?? 0 }}</p>
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
    <!-- ✅ SECTION 1: PENGAJUAN MAHASISWA YANG PERLU APPROVAL DOSEN -->
    <!-- ======================================================================== -->
    @if($pendingApprovals->count() > 0)
    <div class="mb-8 bg-orange-50 rounded-xl shadow-sm border border-orange-300 overflow-hidden">
        <div class="px-6 py-4 border-b border-orange-300 bg-orange-100">
            <h2 class="text-xl font-bold text-orange-800">📋 Menunggu Persetujuan Anda (Mahasiswa)</h2>
            <p class="text-sm text-orange-600 mt-1">Ada {{ $pendingApprovals->total() }} pengajuan dari mahasiswa yang perlu Anda tanggapi</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-orange-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">Tanggal & Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-orange-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-orange-200">
                    @foreach($pendingApprovals as $booking)
                    <tr class="hover:bg-orange-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                                    {{ substr($booking->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $booking->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->user->nim ?? 'N/A' }} • {{ $booking->user->email }}</p>
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
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('booking.show', $booking->id) }}" 
                               class="inline-flex items-center gap-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Review
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($pendingApprovals->hasPages())
        <div class="px-6 py-4 border-t border-orange-200 bg-orange-50">
            {{ $pendingApprovals->links() }}
        </div>
        @endif
    </div>
    @else
    <!-- Empty State untuk Pending Approvals -->
    <div class="mb-8 bg-green-50 rounded-xl shadow-sm border border-green-300 p-6 text-center">
        <svg class="w-16 h-16 mx-auto text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-green-800">Tidak Ada Pengajuan yang Menunggu</h3>
        <p class="text-sm text-green-600 mt-1">Semua pengajuan mahasiswa sudah ditanggapi</p>
    </div>
    @endif

    <!-- ======================================================================== -->
    <!-- ✅ SECTION 2: BOOKING SAYA (Dosen) -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">📚 Booking Saya</h2>
            <a href="{{ route('booking.create-dosen') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                + Booking Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($ownBookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $booking->lab_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $booking->booking_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $booking->session }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                                {{ $booking->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('booking.show', $booking->id) }}" 
                               class="text-blue-600 hover:text-blue-800">Detail</a>
                            @if(!$booking->isConfirmed() && !$booking->isRejected())
                            <form action="{{ route('booking.cancel', $booking->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800 ml-3" 
                                        onclick="return confirm('Yakin ingin membatalkan booking?')">
                                    Batal
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Belum ada booking. <a href="{{ route('booking.create-dosen') }}" class="text-blue-600 hover:underline">Buat booking pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ownBookings->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $ownBookings->links() }}
        </div>
        @endif
    </div>

</div>
@endsection