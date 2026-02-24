<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom-kolom yang diperlukan untuk:
     * - NIM (mahasiswa)
     * - NIP (dosen/teknisi/staff)
     * - Golongan praktikum
     * - Program studi
     * - No. telepon
     * - Lab assignment (untuk teknisi)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NIM untuk mahasiswa (nullable, unique)
            if (!Schema::hasColumn('users', 'nim')) {
                $table->string('nim', 20)->nullable()->unique()->after('email');
            }
            
            // NIP untuk dosen/teknisi/staff (nullable, unique)
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 18)->nullable()->unique()->after('nim');
            }
            
            // Golongan praktikum (A, B, C)
            if (!Schema::hasColumn('users', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('nip');
            }
            
            // Program Studi (default: Teknik Informatika)
            if (!Schema::hasColumn('users', 'prodi')) {
                $table->string('prodi')->default('Teknik Informatika')->after('golongan');
            }
            
            // No. Telepon / WhatsApp
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('prodi');
            }
            
            // Lab assignment untuk teknisi
            // Teknisi akan di-assign ke satu lab tertentu
            // Contoh: 'Mobile', 'MMC', 'KSI', 'AJK', 'RPL'
            if (!Schema::hasColumn('users', 'lab_name')) {
                $table->string('lab_name')->nullable()->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nim',
                'nip',
                'golongan',
                'prodi',
                'phone',
                'lab_name'
            ]);
        });
    }
};