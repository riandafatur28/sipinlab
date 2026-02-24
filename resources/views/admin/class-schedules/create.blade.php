@extends('layouts.app')

@section('title', 'Tambah Jadwal Kuliah - Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Tambah Jadwal Kuliah</h1>
        <p class="text-gray-600">Input jadwal kuliah regular yang menggunakan laboratorium</p>
    </div>

    <form action="{{ route('admin.class-schedules.store') }}" method="POST" class="bg-white rounded-xl shadow-lg p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Mata Kuliah -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Mata Kuliah <span class="text-red-500">*</span></label>
                <input type="text" name="course_name" required value="{{ old('course_name') }}"
                       placeholder="Contoh: Praktikum Pemrograman Web"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Kode MK -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kode Mata Kuliah <span class="text-red-500">*</span></label>
                <input type="text" name="course_code" required value="{{ old('course_code') }}"
                       placeholder="Contoh: TIF123"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Kelas -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas <span class="text-red-500">*</span></label>
                <input type="text" name="class_name" required value="{{ old('class_name') }}"
                       placeholder="Contoh: A / B / C"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- ✅ Golongan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Golongan <span class="text-red-500">*</span></label>
                <select name="golongan" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Golongan --</option>
                    @foreach($golongans as $golongan)
                        <option value="{{ $golongan }}" {{ old('golongan') == $golongan ? 'selected' : '' }}>
                            Golongan {{ $golongan }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Golongan praktikum: A, B, atau C</p>
            </div>

            <!-- Lab -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Laboratorium <span class="text-red-500">*</span></label>
                <select name="lab_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Lab --</option>
                    @foreach($labs as $code => $name)
                        <option value="{{ $name }}" {{ old('lab_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dosen -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dosen Pengampu <span class="text-red-500">*</span></label>
                <select name="lecturer_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Dosen --</option>
                    @foreach($lecturers as $id => $name)
                        <option value="{{ $id }}" {{ old('lecturer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Semester -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Semester <span class="text-red-500">*</span></label>
                <input type="number" name="semester" required min="1" max="14" value="{{ old('semester', 1) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Hari -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hari <span class="text-red-500">*</span></label>
                <select name="day" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Hari --</option>
                    @foreach($days as $day)
                        <option value="{{ $day }}" {{ old('day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sesi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sesi <span class="text-red-500">*</span></label>
                <select name="session" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Sesi --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session }}" {{ old('session') == $session ? 'selected' : '' }}>{{ $session }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Jam Mulai -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" required value="{{ old('start_time') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Jam Selesai -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" required value="{{ old('end_time') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Jumlah Mahasiswa -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Mahasiswa <span class="text-red-500">*</span></label>
                <input type="number" name="students_count" required min="1" value="{{ old('students_count', 30) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>✓ Aktif</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>✗ Non-Aktif</option>
                </select>
            </div>

            <!-- Catatan -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                          placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
            </div>

        </div>

        <div class="flex justify-end gap-4 mt-8">
            <a href="{{ route('admin.class-schedules.index') }}" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400">Batal</a>
            <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 shadow-lg">Simpan Jadwal</button>
        </div>
    </form>
</div>
@endsection