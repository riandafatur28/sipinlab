@extends('layouts.app')

@section('title', 'Kelola Jadwal - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kelola Jadwal Laboratorium</h1>
            <p class="text-gray-600">Pantau dan kelola semua booking</p>
        </div>
        <a href="{{ route('admin.schedule.calendar') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            📅 View Calendar
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.schedule.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <!-- Search -->
            <div>
                <input type="text" name="search" placeholder="Cari nama user..." 
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
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Status Filter -->
            <div class="flex gap-2">
                <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Booking</p>
            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Booking::count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Confirmed</p>
            <p class="text-2xl font-bold text-green-600">{{ \App\Models\Booking::where('status', 'confirmed')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\Booking::where('status', 'pending')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600">{{ \App\Models\Booking::whereDate('booking_date', today())->count() }}</p>
        </div>
    </div>

    <!-- Booking List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sesi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <div>
                                <div class="font-medium text-gray-900">{{ $booking->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($booking->user->role) }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $booking->lab_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->booking_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $booking->session }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'approved_dosen') bg-blue-100 text-blue-800
                                @elseif($booking->status === 'approved_teknisi') bg-indigo-100 text-indigo-800
                                @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                @elseif($booking->status === 'cancelled') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.schedule.show', $booking) }}" 
                                   class="text-blue-600 hover:text-blue-800">Detail</a>
                                <button onclick="openStatusModal({{ $booking->id }}, '{{ $booking->status }}')" 
                                        class="text-green-600 hover:text-green-800">Edit Status</button>
                                @if($booking->status !== 'cancelled' && $booking->status !== 'rejected')
                                <form action="{{ route('admin.schedule.cancel', $booking) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Batalkan booking ini?')" 
                                            class="text-red-600 hover:text-red-800">Batal</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data booking
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $bookings->links() }}
        </div>
    </div>

</div>

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Ubah Status Booking</h3>
        <form id="statusForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                <select name="status" id="statusSelect" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="pending">⏳ Pending</option>
                    <option value="approved_dosen">✅ Disetujui Dosen</option>
                    <option value="approved_teknisi">✅ Disetujui Teknisi</option>
                    <option value="approved_kalab">✅ Disetujui Ka Lab</option>
                    <option value="confirmed">🎉 Confirmed</option>
                    <option value="rejected">❌ Rejected</option>
                    <option value="cancelled">🗑️ Cancelled</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Admin (Opsional)</label>
                <textarea name="admin_note" rows="2" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                          placeholder="Tambahkan catatan..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeStatusModal()" 
                        class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

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

// Close modal when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
</script>
@endsection