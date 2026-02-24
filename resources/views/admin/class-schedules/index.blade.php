@extends('layouts.app')

@section('title', 'Jadwal Kuliah Lab - Admin')

@section('content')
<div class="max-w-7xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Jadwal Kuliah Laboratorium</h1>
            <p class="text-gray-600">Kelola jadwal kuliah regular yang menggunakan lab</p>
        </div>
        <a href="{{ route('admin.class-schedules.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            + Tambah Jadwal
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('admin.class-schedules.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <!-- Search -->
            <div>
                <input type="text" name="search" placeholder="Cari mata kuliah..." value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Lab Filter -->
            <div>
                <select name="lab" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Lab</option>
                    @foreach($labs as $code => $name)
                        <option value="{{ $name }}" {{ request('lab') == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Golongan Filter -->
            <div>
                <select name="golongan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Golongan</option>
                    @foreach($golongans as $gol)
                        <option value="{{ $gol }}" {{ request('golongan') == $gol ? 'selected' : '' }}>Golongan {{ $gol }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Day Filter -->
            <div>
                <select name="day" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Hari</option>
                    @foreach($days as $day)
                        <option value="{{ $day }}" {{ request('day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Status + Submit -->
            <div class="flex gap-2">
                <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 whitespace-nowrap">Filter</button>
            </div>
        </form>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Jadwal</p>
            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\ClassSchedule::count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Jadwal Aktif</p>
            <p class="text-2xl font-bold text-green-600">{{ \App\Models\ClassSchedule::where('status', 'active')->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Mahasiswa</p>
            <p class="text-2xl font-bold text-purple-600">{{ \App\Models\ClassSchedule::sum('students_count') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Hari Ini</p>
            <p class="text-2xl font-bold text-orange-600">{{ \App\Models\ClassSchedule::where('day', \Carbon\Carbon::now()->isoFormat('dddd'))->where('status', 'active')->count() }}</p>
        </div>
    </div>

    <!-- Schedule List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gol</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosen</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900">{{ $schedule->course_name }}</div>
                            <div class="text-xs text-gray-500">{{ $schedule->course_code }} | Kelas {{ $schedule->class_name }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $schedule->lab_name }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                @if($schedule->golongan === 'A') bg-blue-100 text-blue-800
                                @elseif($schedule->golongan === 'B') bg-green-100 text-green-800
                                @elseif($schedule->golongan === 'C') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $schedule->golongan }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $schedule->day }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $schedule->lecturer->name }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($schedule->status === 'active')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">✓ Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">✗ Non-Aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.class-schedules.edit', $schedule) }}" class="text-green-600 hover:text-green-800 font-medium">Edit</a>
                                <form action="{{ route('admin.class-schedules.destroy', $schedule) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus jadwal ini?')" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            Belum ada jadwal kuliah. <a href="{{ route('admin.class-schedules.create') }}" class="text-blue-600 hover:underline">Tambahkan pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            {{ $schedules->links() }}
        </div>
    </div>

</div>
@endsection