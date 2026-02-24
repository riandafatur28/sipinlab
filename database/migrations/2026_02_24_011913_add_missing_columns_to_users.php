<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ Hanya tambah jika kolom belum ada
            if (!Schema::hasColumn('users', 'nim')) {
                $table->string('nim', 20)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('nim');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('golongan');
            }
            if (!Schema::hasColumn('users', 'prodi')) {
                $table->string('prodi')->default('Teknik Informatika')->after('phone');
            }
        });
    }

    public function down(): void
    {
        // Optional: tidak perlu drop jika kolom memang sudah ada dari awal
    }
};