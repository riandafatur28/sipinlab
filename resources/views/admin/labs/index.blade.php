@extends('layouts.app')

@section('title', 'Kelola Laboratorium - Admin')

@section('content')
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kelola Laboratorium</h1>
            <p class="text-gray-600">Manajemen data laboratorium</p>
        </div>
        <a href="{{ route('admin.labs.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            + Tambah Lab
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Laboratorium</p>
            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Lab::count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Aktif</p>
            <p class="text-2xl font-bold text-green-600">{{ \App\Models\Lab::where('status', 'active')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Non-Aktif</p>
            <p class="text-2xl font-bold text-gray-600">{{ \App\Models\Lab::where('status', 'inactive')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Kapasitas</p>
            <p class="text-2xl font-bold text-purple-600">{{ \App\Models\Lab::sum('capacity') }}</p>
        </div>
    </div>

    <!-- Lab List -->
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
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                                {{ $lab->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $lab->name }}</div>
                            @if($lab->description)
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $lab->description }}</div>
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
                                <a href="{{ route('admin.labs.edit', $lab) }}" class="text-green-600 hover:text-green-800">Edit</a>
                                <form action="{{ route('admin.labs.destroy', $lab) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus laboratorium {{ $lab->name }}?')"
                                            class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Belum ada data laboratorium. <a href="{{ route('admin.labs.create') }}" class="text-blue-600 hover:underline">Tambahkan pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $labs->links() }}
        </div>
    </div>

</div>
@endsection
