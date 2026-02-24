@extends('layouts.app')

@section('title', 'Detail Booking - Admin')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Booking</h1>
            <p class="text-gray-600">ID: #{{ $booking->id }}</p>
        </div>
        <a href="{{ route('admin.schedule.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Kembali
        </a>
    </div>

    <!-- Booking Info Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- User Info -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">👤 Pemohon</h3>
                <div class="space-y-2 text-sm">
                    <p><span class="text-gray-500">Nama:</span> <strong>{{ $booking->user->name }}</strong></p>
                    <p><span class="text-gray-500">Email:</span> {{ $booking->user->email }}</p>
                    <p><span class="text-gray-500">Role:</span> {{ ucfirst($booking->user->role) }}</p>
                    @if($booking->supervisor)
                        <p><span class="text-gray-500">Dosen Pembimbing:</span> {{ $booking->supervisor->name }}</p>
                    @endif
                </div>
            </div>
            
            <!-- Booking Details -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-3">📅 Detail Booking</h3>
                <div class="space-y-2 text-sm">
                    <p><span class="text-gray-500">Lab:</span> <strong>{{ $booking->lab_name }}</strong></p>
                    <p><span class="text-gray-500">Tanggal:</span> {{ $booking->booking_date->format('l, d F Y') }}</p>
                    <p><span class="text-gray-500">Sesi:</span> {{ $booking->session }}</p>
                    <p><span class="text-gray-500">Waktu:</span> {{ $booking->start_time }} - {{ $booking->end_time }}</p>
                </div>
            </div>
            
        </div>
        
        <!-- Purpose -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="font-semibold text-gray-700 mb-2">📋 Keperluan</h3>
            <p class="text-sm text-gray-600">{{ $booking->purpose }}</p>
        </div>
        
        <!-- Status Timeline -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="font-semibold text-gray-700 mb-4">🔄 Status Timeline</h3>
            <div class="space-y-3 text-sm">
                
                @if($booking->created_at)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full bg-blue-500"></div>
                    <div>
                        <p class="font-medium">Booking Dibuat</p>
                        <p class="text-gray-500">{{ $booking->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif
                
                @if($booking->approved_at_dosen)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                    <div>
                        <p class="font-medium">Disetujui Dosen</p>
                        <p class="text-gray-500">{{ $booking->approved_at_dosen->format('d M Y H:i') }} 
                            @if($booking->dosen) oleh {{ $booking->dosen->name }} @endif
                        </p>
                    </div>
                </div>
                @endif
                
                @if($booking->approved_at_teknisi)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                    <div>
                        <p class="font-medium">Disetujui Teknisi</p>
                        <p class="text-gray-500">{{ $booking->approved_at_teknisi->format('d M Y H:i') }}
                            @if($booking->teknisi) oleh {{ $booking->teknisi->name }} @endif
                        </p>
                    </div>
                </div>
                @endif
                
                @if($booking->approved_at_kalab)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                    <div>
                        <p class="font-medium">Disetujui Ka Lab</p>
                        <p class="text-gray-500">{{ $booking->approved_at_kalab->format('d M Y H:i') }}
                            @if($booking->kalab) oleh {{ $booking->kalab->name }} @endif
                        </p>
                    </div>
                </div>
                @endif
                
                @if($booking->rejected_at)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 mt-2 rounded-full bg-red-500"></div>
                    <div>
                        <p class="font-medium text-red-600">Ditolak</p>
                        <p class="text-gray-500">{{ $booking->rejected_at->format('d M Y H:i') }}
                            @if($booking->rejection_reason) - {{ $booking->rejection_reason }} @endif
                        </p>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
        
        <!-- Current Status -->
        <div class="mt-6 p-4 rounded-lg 
            @if($booking->status === 'confirmed') bg-green-50 border border-green-200
            @elseif($booking->status === 'pending') bg-yellow-50 border border-yellow-200
            @elseif($booking->status === 'rejected' || $booking->status === 'cancelled') bg-red-50 border border-red-200
            @else bg-blue-50 border border-blue-200 @endif">
            <p class="font-semibold">
                Status Saat Ini: 
                <span class="@if($booking->status === 'confirmed') text-green-700
                    @elseif($booking->status === 'pending') text-yellow-700
                    @elseif($booking->status === 'rejected' || $booking->status === 'cancelled') text-red-700
                    @else text-blue-700 @endif">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </span>
            </p>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4">⚙️ Aksi Admin</h3>
        
        <div class="flex flex-wrap gap-3">
            <!-- Quick Status Change -->
            <button onclick="openStatusModal({{ $booking->id }}, '{{ $booking->status }}')" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                ✏️ Ubah Status
            </button>
            
            <!-- Cancel Button -->
            @if(!in_array($booking->status, ['cancelled', 'rejected']))
            <form action="{{ route('admin.schedule.cancel', $booking) }}" method="POST" class="inline">
                @csrf
                <button type="submit" onclick="return confirm('Batalkan booking ini?')" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    🗑️ Batalkan
                </button>
            </form>
            @endif
            
            <!-- View User Profile -->
            <a href="{{ route('admin.users.show', $booking->user) }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                👤 Lihat Profil User
            </a>
        </div>
    </div>

</div>

<!-- Include the same status modal script from index -->
<script>
let currentBookingId = null;

function openStatusModal(bookingId, currentStatus) {
    currentBookingId = bookingId;
    document.getElementById('statusSelect').value = currentStatus;
    document.getElementById('statusForm').action = '/admin/schedule/' + bookingId + '/update-status';
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    currentBookingId = null;
}

document.getElementById('statusModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
</script>
@endsection