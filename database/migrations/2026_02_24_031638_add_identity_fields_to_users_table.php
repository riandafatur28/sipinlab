<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ NIM untuk mahasiswa (nullable, unique)
            if (!Schema::hasColumn('users', 'nim')) {
                $table->string('nim', 20)->nullable()->unique()->after('email');
            }
            
            // ✅ NIP untuk dosen (nullable, unique)
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 18)->nullable()->unique()->after('nim');
            }
            
            // ✅ Golongan praktikum
            if (!Schema::hasColumn('users', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('nip');
            }
            
            // ✅ Program Studi
            if (!Schema::hasColumn('users', 'prodi')) {
                $table->string('prodi')->default('Teknik Informatika')->after('golongan');
            }
            
            // ✅ No. Telepon
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('prodi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'golongan', 'prodi', 'phone']);
        });
    }
};