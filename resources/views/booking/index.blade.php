@extends('layouts.app')

@section('title', 'Kelola Jadwal Laboratorium')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📋 Kelola Jadwal Laboratorium</h1>
            <p class="text-gray-600 mt-1">Pantau dan kelola semua booking laboratorium</p>
        </div>

        @if(Auth::user()->isAdmin())
        <a href="{{ route('admin.class-schedules.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            View Calendar
        </a>
        @endif
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form action="{{ route('booking.index') }}" method="GET" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                <!-- Search Input -->
                <div class="lg:col-span-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama user..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                <!-- Lab Dropdown -->
                <div class="lg:col-span-1">
                    <select name="lab" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                        <option value="">Semua Lab</option>
                        @foreach($labs as $lab)
                            <option value="{{ $lab }}" {{ request('lab') == $lab ? 'selected' : '' }}>{{ $lab }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Start Date -->
                <div class="lg:col-span-1">
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <span class="text-xs text-gray-400 block mt-1">Dari</span>
                </div>

                <!-- End Date -->
                <div class="lg:col-span-1">
                    <input type="date" name="date_end" value="{{ request('date_end') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <span class="text-xs text-gray-400 block mt-1">Sampai</span>
                </div>

                <!-- Status Select -->
                <div class="lg:col-span-1">
                    <select name="status" onchange="this.form.submit()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved_dosen" {{ request('status') == 'approved_dosen' ? 'selected' : '' }}>Approved Dosen</option>
                        <option value="approved_teknisi" {{ request('status') == 'approved_teknisi' ? 'selected' : '' }}>Approved Teknisi</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- Reset Button -->
                @if(request('search') || request('lab') || request('date_start') || request('date_end') || request('status'))
                <div class="lg:col-span-1 flex items-end">
                     <a href="{{ route('booking.index') }}" class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm text-center">Reset</a>
                </div>
                @endif

            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Booking -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Total Booking</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_booking'] ?? 0 }}</p>
        </div>

        <!-- Confirmed -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Confirmed</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['confirmed'] ?? 0 }}</p>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Pending Approval</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
        </div>

        <!-- Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-1">Hari Ini</p>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['hari_ini'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {!! session('success') !!}
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($booking->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-sm">{{ $booking->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($booking->user->role) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $booking->lab_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->session }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                                {{ $booking->getStatusLabel() }}
                            </span>
                        </td>

                        <!-- ✅ BAGIAN AKSI YANG DIPERBARUI -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">

                            {{-- Detail Link --}}
                            <a href="{{ route('booking.show', $booking->id) }}"
                               class="text-blue-600 hover:text-blue-800 font-medium mr-3">
                                Detail
                            </a>

                            {{-- Aksi Approve Teknisi --}}
                            @if(Auth::user()->isTeknisi() && ($booking->status === 'approved_dosen' || $booking->status === 'pending'))
                                <form action="{{ route('booking.approve-teknisi', $booking->id) }}" method="POST" class="inline" onclick="return confirm('Setujui booking ini?')">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 mr-2">Setuju</button>
                                </form>
                            @endif

                            {{-- Aksi Konfirmasi Kalab --}}
                            @if((Auth::user()->isKalab() || Auth::user()->role === 'ketua_lab') && $booking->status === 'approved_teknisi')
                                <form action="{{ route('booking.approve-kalab', $booking->id) }}" method="POST" class="inline" onclick="return confirm('Konfirmasi Final?')">
                                    @csrf
                                    <button type="submit" class="text-purple-600 hover:text-purple-800 mr-2">Konfirmasi</button>
                                </form>
                            @endif

                            {{-- Aksi Hapus / Batal (Jika belum final) --}}
                            @if(Auth::user()->isAdmin() || Auth::user()->isKalab() || Auth::user()->isTeknisi())
                                @if(!in_array($booking->status, ['confirmed']))
                                <form action="{{ route('booking.destroy', $booking->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Yakin hapus booking ini?')" class="text-red-600 hover:text-red-800 ml-2">Batal</button>
                                </form>
                                @endif
                            @endif

                            {{-- ✅ BARU: Tombol Unduh PDF (Hanya jika CONFIRMED & User Admin/Kalab) --}}
                            @if($booking->status === 'confirmed')
                                <span class="mx-2 text-gray-300 border-r"></span>

                                @if(Auth::user()->isAdmin() || Auth::user()->isKalab() || Auth::user()->role === 'ketua_lab')
                                    <a href="{{ route('booking.download-pdf', $booking->id) }}"
                                       target="_blank"
                                       class="text-pink-600 hover:text-pink-800 font-medium flex items-center gap-1"
                                       title="Unduh Formulir Resmi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Unduh Form
                                    </a>
                                @else
                                    <span class="ml-2 text-gray-400 italic text-xs">Formulir Selesai</span>
                                @endif
                            @endif

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                            Tidak ada data booking yang ditemukan. Silakan reset filter atau tambah booking baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $bookings->links() }}
        </div>
    </div>

</div>
@endsection
