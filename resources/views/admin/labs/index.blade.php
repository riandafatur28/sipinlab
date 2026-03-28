@extends('layouts.app')

@section('title', 'Kelola Laboratorium - Admin')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header dengan Info Role -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🏢 Kelola Laboratorium</h1>
            <p class="text-gray-600">Manajemen data laboratorium</p>
        </div>

        @if(Auth::user()->isKalab())
            <div class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-lg text-sm font-medium mr-4">
                👔 Mode Kalab: {{ Auth::user()->lab_name ?? 'Semua Lab' }}
            </div>
        @endif

        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.labs.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                + Tambah Lab
            </a>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Laboratorium</p>
            <p class="text-2xl font-bold text-blue-600">{{ $labs->total() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Aktif</p>
            <p class="text-2xl font-bold text-green-600">{{ $labs->filter(fn($l) => $l->status === 'active')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Non-Aktif</p>
            <p class="text-2xl font-bold text-gray-600">{{ $labs->filter(fn($l) => $l->status === 'inactive')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Kapasitas</p>
            <p class="text-2xl font-bold text-purple-600">{{ $labs->sum('capacity') }}</p>
        </div>
    </div>

    <!-- Search & Filter -->
    @if(Auth::user()->isAdmin())
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.labs.index') }}" class="flex flex-wrap gap-4">
            <input type="text" name="search" placeholder="Cari nama/kode lab..."
                   value="{{ request('search') }}"
                   class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">

            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
            </select>

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                🔍 Filter
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.labs.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    🔄 Reset
                </a>
            @endif
        </form>
    </div>
    @endif

    <!-- Lab List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Laboratorium</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($labs as $lab)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                                {{ $lab->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $lab->name }}</div>
                            @if($lab->description)
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($lab->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $lab->location ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $lab->capacity }} orang</td>
                        <td class="px-6 py-4">
                            @if($lab->status === 'active')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">
                                    ✓ Aktif
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">
                                    ✗ Non-Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.labs.edit', $lab) }}"
                                   class="text-green-600 hover:text-green-800 font-medium">✏️ Edit</a>
                                @if(Auth::user()->isAdmin())
                                    <form action="{{ route('admin.labs.destroy', $lab) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus laboratorium {{ $lab->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">🗑️ Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <p>Belum ada data laboratorium.</p>
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.labs.create') }}" class="text-blue-600 hover:underline font-medium">
                                        + Tambahkan laboratorium pertama
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($labs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $labs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
