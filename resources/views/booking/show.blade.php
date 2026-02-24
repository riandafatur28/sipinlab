@extends('layouts.app')

@section('title', 'Detail Booking')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('booking.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
        <h1 class="text-3xl font-bold text-gray-800">📋 Detail Booking</h1>
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

    <!-- Booking Info Card -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden mb-8">
        <!-- Header dengan Status -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $booking->lab_name }}</h2>
                <p class="text-sm text-gray-600">{{ $booking->session }} • {{ $booking->booking_date->format('d M Y') }}</p>
            </div>
            <span class="px-4 py-2 text-sm font-semibold rounded-full {{ $booking->getStatusBadgeClass() }}">
                {{ $booking->getStatusLabel() }}
            </span>
        </div>

        <div class="p-6">
            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Pemohon -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">👤 Pemohon</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="font-semibold text-gray-800">{{ $booking->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $booking->user->role === 'mahasiswa' ? 'NIM: ' . ($booking->user->nim ?? 'N/A') : 'NIP: ' . ($booking->user->nip ?? 'N/A') }}</p>
                        <p class="text-sm text-gray-600">{{ $booking->user->email }}</p>
                        <p class="text-sm text-gray-600">📞 {{ $booking->phone }}</p>
                    </div>
                </div>

                <!-- Waktu & Tanggal -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">📅 Waktu & Tanggal</h3>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Tanggal:</strong> {{ $booking->booking_date->format('d M Y') }}</p>
                        <p class="text-sm text-gray-700"><strong>Sesi:</strong> {{ $booking->session }}</p>
                        <p class="text-sm text-gray-700"><strong>Durasi:</strong> {{ $booking->duration_days }} hari</p>
                        <p class="text-sm text-gray-700"><strong>Periode:</strong> {{ $booking->start_date->format('d M') }} - {{ $booking->end_date->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Kegiatan -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">📚 Kegiatan</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="font-semibold text-gray-800">{{ $booking->activity }}</p>
                        <p class="text-sm text-gray-600 mt-2">{{ $booking->purpose }}</p>
                    </div>
                </div>

                <!-- Anggota (jika ada) -->
                @if($booking->is_group && $booking->membersCollection->count() > 0)
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">👥 Anggota Kelompok</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <ul class="space-y-1 text-sm text-gray-700">
                            @foreach($booking->membersCollection as $member)
                            <li>• {{ $member->name }} ({{ $member->nim ?? 'N/A' }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Supervisor -->
                @if($booking->supervisor)
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">👨‍🏫 Dosen Pembimbing</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="font-semibold text-gray-800">{{ $booking->supervisor->name }}</p>
                        <p class="text-sm text-gray-600">{{ $booking->supervisor->email }}</p>
                    </div>
                </div>
                @endif

                <!-- Peralatan -->
                @if($booking->notes)
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">🔧 Kebutuhan Peralatan</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700">{{ $booking->notes }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Approval Timeline -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">📊 Timeline Persetujuan</h3>
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full {{ $booking->isApprovedByDosen() ? 'bg-green-500' : ($booking->user->role === 'dosen' ? 'bg-gray-300' : 'bg-yellow-500') }} text-white flex items-center justify-center text-xs font-bold">
                                {{ $booking->isApprovedByDosen() ? '✓' : '1' }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Dosen</p>
                                <p class="text-xs text-gray-500">{{ $booking->approved_at_dosen ? $booking->approved_at_dosen->format('d M Y H:i') : 'Menunggu' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-16 h-1 bg-gray-300 {{ $booking->isApprovedByDosen() ? 'bg-green-500' : '' }}"></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full {{ $booking->isApprovedByTeknisi() ? 'bg-green-500' : ($booking->isApprovedByDosen() ? 'bg-yellow-500' : 'bg-gray-300') }} text-white flex items-center justify-center text-xs font-bold">
                                {{ $booking->isApprovedByTeknisi() ? '✓' : '2' }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Teknisi</p>
                                <p class="text-xs text-gray-500">{{ $booking->approved_at_teknisi ? $booking->approved_at_teknisi->format('d M Y H:i') : 'Menunggu' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-16 h-1 bg-gray-300 {{ $booking->isApprovedByTeknisi() ? 'bg-green-500' : '' }}"></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full {{ $booking->isConfirmed() ? 'bg-green-500' : ($booking->isApprovedByTeknisi() ? 'bg-yellow-500' : 'bg-gray-300') }} text-white flex items-center justify-center text-xs font-bold">
                                {{ $booking->isConfirmed() ? '✓' : '3' }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Ka Lab</p>
                                <p class="text-xs text-gray-500">{{ $booking->approved_at_kalab ? $booking->approved_at_kalab->format('d M Y H:i') : 'Menunggu' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION BUTTONS untuk Teknisi -->
    @if(Auth::user()->role === 'teknisi' && $booking->canApproveByCurrentTeknisi())
    <div class="bg-orange-50 rounded-xl shadow-sm border border-orange-300 p-6 mb-8">
        <h3 class="text-lg font-bold text-orange-800 mb-4">⚠️ Perlu Tindakan Anda (Teknisi)</h3>
        <p class="text-sm text-orange-700 mb-6">
            Booking ini menunggu persetujuan teknisi untuk lab <strong>{{ $booking->lab_name }}</strong>. 
            Silakan review dan berikan tanggapan.
        </p>
        
        <div class="flex gap-4">
            <!-- Approve Button -->
            <form action="{{ route('booking.approve-teknisi', $booking->id) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" 
                        class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Setujui Booking
                </button>
            </form>

            <!-- Reject Button -->
            <button onclick="document.getElementById('rejectModal').classList.remove('hidden')" 
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Tolak Booking
            </button>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">❌ Tolak Booking</h3>
            <form action="{{ route('booking.reject', $booking->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" required rows="4" maxlength="500"
                              placeholder="Jelaskan alasan penolakan booking ini..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg font-medium">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                        Tolak Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ACTION BUTTONS untuk Ka Lab -->
    @if((Auth::user()->role === 'ketua_lab' || Auth::user()->role === 'admin') && $booking->canApproveByKalab())
    <div class="bg-purple-50 rounded-xl shadow-sm border border-purple-300 p-6 mb-8">
        <h3 class="text-lg font-bold text-purple-800 mb-4">⚠️ Perlu Konfirmasi Final (Ka Lab)</h3>
        <p class="text-sm text-purple-700 mb-6">
            Booking ini sudah disetujui teknisi dan menunggu konfirmasi final dari Ka Lab.
        </p>
        
        <div class="flex gap-4">
            <!-- Approve Button -->
            <form action="{{ route('booking.approve-kalab', $booking->id) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" 
                        class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Konfirmasi Final
                </button>
            </form>

            <!-- Reject Button -->
            <button onclick="document.getElementById('rejectModal').classList.remove('hidden')" 
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Tolak Booking
            </button>
        </div>
    </div>
    @endif

</div>
@endsection