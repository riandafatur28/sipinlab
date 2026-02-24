<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('lab_name'); // Nama lab
            $table->string('course_name'); // Nama mata kuliah
            $table->string('course_code'); // Kode mata kuliah
            $table->string('class_name'); // Nama kelas (A, B, C, dll)
            $table->foreignId('lecturer_id')->constrained('users')->onDelete('cascade'); // Dosen pengampu
            $table->integer('semester'); // Semester
            $table->string('day'); // Hari (Senin, Selasa, dll)
            $table->time('start_time'); // Jam mulai
            $table->time('end_time'); // Jam selesai
            $table->string('session'); // Sesi (Sesi 1, Sesi 2, dll)
            $table->integer('students_count')->default(0); // Jumlah mahasiswa
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};