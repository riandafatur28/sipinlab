@extends('layouts.app')

@section('title', 'Edit Jadwal Kuliah - Admin')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Edit Jadwal Kuliah</h1>
        <p class="text-gray-600">Update informasi jadwal praktikum laboratorium</p>
    </div>

    <!-- Current Schedule Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-800">
                    ID Jadwal: #{{ $classSchedule->id }} 
                    <span class="mx-2">•</span>
                    Dibuat: {{ $classSchedule->created_at->format('d M Y') }}
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.class-schedules.update', $classSchedule) }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Mata Kuliah -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Mata Kuliah <span class="text-red-500">*</span></label>
                <input type="text" name="course_name" required value="{{ old('course_name', $classSchedule->course_name) }}"
                       placeholder="Contoh: Praktikum Pemrograman Web"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('course_name') border-red-500 @enderror">
                @error('course_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Kode MK -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Mata Kuliah <span class="text-red-500">*</span></label>
                <input type="text" name="course_code" required value="{{ old('course_code', $classSchedule->course_code) }}"
                       placeholder="Contoh: TIF123" maxlength="20"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('course_code') border-red-500 @enderror">
                @error('course_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Kelas -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas <span class="text-red-500">*</span></label>
                <input type="text" name="class_name" required value="{{ old('class_name', $classSchedule->class_name) }}"
                       placeholder="Contoh: A / B / C" maxlength="20"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('class_name') border-red-500 @enderror">
                @error('class_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Golongan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Golongan <span class="text-red-500">*</span></label>
                <select name="golongan" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('golongan') border-red-500 @enderror">
                    <option value="">-- Pilih Golongan --</option>
                    @foreach($golongans as $gol)
                        <option value="{{ $gol }}" {{ old('golongan', $classSchedule->golongan) == $gol ? 'selected' : '' }}>
                            Golongan {{ $gol }}
                        </option>
                    @endforeach
                </select>
                @error('golongan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-500">Golongan praktikum: A, B, atau C</p>
            </div>

            <!-- Lab -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium <span class="text-red-500">*</span></label>
                <select name="lab_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('lab_name') border-red-500 @enderror">
                    <option value="">-- Pilih Lab --</option>
                    @foreach($labs as $code => $name)
                        <option value="{{ $name }}" {{ old('lab_name', $classSchedule->lab_name) == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('lab_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Dosen -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dosen Pengampu <span class="text-red-500">*</span></label>
                <select name="lecturer_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('lecturer_id') border-red-500 @enderror">
                    <option value="">-- Pilih Dosen --</option>
                    @foreach($lecturers as $id => $name)
                        <option value="{{ $id }}" {{ old('lecturer_id', $classSchedule->lecturer_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('lecturer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Semester -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Semester <span class="text-red-500">*</span></label>
                <input type="number" name="semester" required min="1" max="14" value="{{ old('semester', $classSchedule->semester) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('semester') border-red-500 @enderror">
                @error('semester') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Hari -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari <span class="text-red-500">*</span></label>
                <select name="day" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('day') border-red-500 @enderror">
                    <option value="">-- Pilih Hari --</option>
                    @foreach($days as $day)
                        <option value="{{ $day }}" {{ old('day', $classSchedule->day) == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
                @error('day') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-500">Hari praktikum: Senin - Jumat</p>
            </div>

            <!-- Sesi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sesi <span class="text-red-500">*</span></label>
                <select name="session" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('session') border-red-500 @enderror">
                    <option value="">-- Pilih Sesi --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session }}" {{ old('session', $classSchedule->session) == $session ? 'selected' : '' }}>{{ $session }}</option>
                    @endforeach
                </select>
                @error('session') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Jam Mulai -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" required value="{{ old('start_time', $classSchedule->start_time) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('start_time') border-red-500 @enderror">
                @error('start_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Jam Selesai -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" required value="{{ old('end_time', $classSchedule->end_time) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('end_time') border-red-500 @enderror">
                @error('end_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Jumlah Mahasiswa -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Mahasiswa <span class="text-red-500">*</span></label>
                <input type="number" name="students_count" required min="1" max="200" value="{{ old('students_count', $classSchedule->students_count) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('students_count') border-red-500 @enderror">
                @error('students_count') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                    <option value="active" {{ old('status', $classSchedule->status) == 'active' ? 'selected' : '' }}>✓ Aktif</option>
                    <option value="inactive" {{ old('status', $classSchedule->status) == 'inactive' ? 'selected' : '' }}>✗ Non-Aktif</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Catatan -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror"
                          placeholder="Catatan tambahan (opsional)">{{ old('notes', $classSchedule->notes) }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.class-schedules.index') }}" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-lg">
                💾 Update Jadwal
            </button>
        </div>
    </form>

    <!-- Delete Button (Separate) -->
    <div class="mt-6 text-right">
        <form action="{{ route('admin.class-schedules.destroy', $classSchedule) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('⚠️ Yakin ingin menghapus jadwal ini?\n\n{{ $classSchedule->course_name }} - Golongan {{ $classSchedule->golongan }}\n{{ $classSchedule->day }}, {{ $classSchedule->start_time }} - {{ $classSchedule->end_time }}')" 
                    class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center gap-1 ml-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Hapus Jadwal Ini
            </button>
        </form>
    </div>

</div>
@endsection