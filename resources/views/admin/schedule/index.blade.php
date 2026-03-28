@extends('layouts.app')

@section('title', 'Kelola Jadwal - Admin')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header dengan Info Role -->
    <div class="mb-8 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📋 Kelola Jadwal Laboratorium</h1>
            <p class="text-gray-600">Pantau dan kelola semua booking</p>
        </div>

        <!-- Role Badge -->
        @if(Auth::user()->isKalab() || Auth::user()->isTeknisi())
            <div class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-lg text-sm font-medium">
                @if(Auth::user()->isKalab())
                    👔 Mode Kalab: {{ Auth::user()->lab_name ?? 'Semua Lab' }}
                @else
                    🔧 Mode Teknisi: {{ Auth::user()->lab_name ?? 'Semua Lab' }}
                @endif
            </div>
        @endif
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.schedule.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

            <!-- Search -->
            <div class="lg:col-span-2">
                <input type="text" name="search" placeholder="Cari nama user, NIM, NIP..."
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Lab Filter -->
            <div>
                <select name="lab" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Lab</option>
                    @foreach($labs as $code => $name)
                        <option value="{{ $name }}" {{ request('lab') == $name ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="Dari">
            </div>
            <div>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="Sampai">
            </div>

            <!-- Status Filter + Submit -->
            <div class="flex gap-2">
                <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="approved_dosen" {{ request('status') == 'approved_dosen' ? 'selected' : '' }}>✅ Dosen</option>
                    <option value="approved_teknisi" {{ request('status') == 'approved_teknisi' ? 'selected' : '' }}>✅ Teknisi</option>
                    <option value="approved_kalab" {{ request('status') == 'approved_kalab' ? 'selected' : '' }}>✅ Kalab</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>🎉 Confirmed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>🗑️ Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    🔍
                </button>
            </div>
        </form>

        @if(request()->anyFilled(['search', 'lab', 'start_date', 'end_date', 'status']))
        <div class="mt-3 pt-3 border-t border-gray-200">
            <a href="{{ route('admin.schedule.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                🔄 Reset semua filter
            </a>
        </div>
        @endif
    </div>

    <!-- Stats Cards -->
    @if(isset($stats))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Booking</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_booking'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Confirmed</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['confirmed'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['hari_ini'] ?? 0 }}</p>
        </div>
    </div>
    @endif

    <!-- Booking List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sesi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm">
                            <div>
                                <div class="font-medium text-gray-900">{{ $booking->user->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ ucfirst($booking->user->role) }}
                                    @if($booking->user->role === 'mahasiswa' && $booking->user->nim)
                                        • {{ $booking->user->nim }}
                                    @elseif($booking->user->role !== 'mahasiswa' && $booking->user->nip)
                                        • {{ $booking->user->nip }}
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $booking->lab_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('DD MMM YYYY') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $booking->session }}<br>
                            <span class="text-xs text-gray-400">
                                {{ $booking->start_time }} - {{ $booking->end_time }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'approved_dosen') bg-blue-100 text-blue-800
                                @elseif($booking->status === 'approved_teknisi') bg-indigo-100 text-indigo-800
                                @elseif($booking->status === 'approved_kalab') bg-purple-100 text-purple-800
                                @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                @elseif($booking->status === 'cancelled') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex flex-col gap-1">
                                <a href="{{ route('admin.schedule.show', $booking) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">👁️ Detail</a>
                                <button onclick="openStatusModal({{ $booking->id }}, '{{ $booking->status }}')"
                                        class="text-green-600 hover:text-green-800 font-medium">✏️ Status</button>

                                <!-- ✅ Cancel Button: Hanya Admin/Kalab yang bisa cancel -->
                                @if(!Auth::user()->isTeknisi() && !in_array($booking->status, ['cancelled', 'rejected']))
                                <form action="{{ route('admin.schedule.cancel', $booking) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Batalkan booking ini? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">🗑️ Batal</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p>Tidak ada data booking yang sesuai filter.</p>
                                <a href="{{ route('admin.schedule.index') }}" class="text-blue-600 hover:underline">
                                    🔄 Reset filter
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>

</div>

<!-- ======================================================================== -->
<!-- ✅ MODAL: Update Status Booking -->
<!-- ======================================================================== -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">✏️ Ubah Status Booking</h3>
            <button type="button" onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="statusForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                <select name="status" id="statusSelect" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="pending">⏳ Pending</option>
                    <option value="approved_dosen">✅ Disetujui Dosen</option>

                    <!-- ✅ Teknisi: hanya bisa sampai approved_teknisi -->
                    @if(Auth::user()->isTeknisi())
                        <option value="approved_teknisi">✅ Disetujui Teknisi (Final)</option>
                        <option value="rejected">❌ Rejected</option>
                        <!-- Teknisi tidak bisa set ke approved_kalab, confirmed, cancelled -->
                    @else
                        <!-- Admin/Kalab: semua status tersedia -->
                        <option value="approved_teknisi">✅ Disetujui Teknisi</option>
                        <option value="approved_kalab">✅ Disetujui Ka Lab</option>
                        <option value="confirmed">🎉 Confirmed</option>
                        <option value="rejected">❌ Rejected</option>
                        <option value="cancelled">🗑️ Cancelled</option>
                    @endif
                </select>
                @if(Auth::user()->isTeknisi())
                    <p class="text-xs text-indigo-600 mt-1">
                        ℹ️ Teknisi hanya dapat menyetujui sampai tahap "Disetujui Teknisi". Approval final dilakukan oleh Ka Lab.
                    </p>
                @endif
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea name="admin_note" rows="2"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                          placeholder="Tambahkan catatan untuk user..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeStatusModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">✅ Update</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================== -->
<!-- ======================================================================== -->
<script>
let currentBookingId = null;

function openStatusModal(bookingId, currentStatus) {
    currentBookingId = bookingId;
    const statusSelect = document.getElementById('statusSelect');

    // Set current status
    statusSelect.value = currentStatus;

    // ✅ Disable options that Teknisi cannot select
    const isTeknisi = {{ Auth::user()->isTeknisi() ? 'true' : 'false' }};
    if (isTeknisi) {
        const forbiddenValues = ['approved_kalab', 'confirmed', 'cancelled'];
        Array.from(statusSelect.options).forEach(option => {
            if (forbiddenValues.includes(option.value)) {
                option.disabled = true;
                option.style.display = 'none';
            }
        });
    }

    // Set form action
    document.getElementById('statusForm').action = '/admin/schedule/' + bookingId + '/update-status';

    // Show modal
    document.getElementById('statusModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentBookingId = null;

    // Re-enable all options for next open
    const statusSelect = document.getElementById('statusSelect');
    Array.from(statusSelect.options).forEach(option => {
        option.disabled = false;
        option.style.display = '';
    });
}

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});

// Close with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
    }
});

// Prevent form submit on Enter in textarea
document.querySelector('#statusForm textarea')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
    }
});

// ✅ Auto-hide forbidden options on page load (for direct access)
document.addEventListener('DOMContentLoaded', function() {
    const isTeknisi = {{ Auth::user()->isTeknisi() ? 'true' : 'false' }};
    if (isTeknisi) {
        const statusSelect = document.getElementById('statusSelect');
        if (statusSelect) {
            const forbiddenValues = ['approved_kalab', 'confirmed', 'cancelled'];
            Array.from(statusSelect.options).forEach(option => {
                if (forbiddenValues.includes(option.value)) {
                    option.disabled = true;
                    option.style.display = 'none';
                }
            });
        }
    }
});
</script>

@endsection
