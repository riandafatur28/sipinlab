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
        Schema::table('bookings', function (Blueprint $table) {
            // ✅ Tambah kolom phone jika belum ada
            if (!Schema::hasColumn('bookings', 'phone')) {
                $table->string('phone', 20)->nullable()->after('purpose');
            }
            
            // ✅ Tambah kolom prodi jika belum ada
            if (!Schema::hasColumn('bookings', 'prodi')) {
                $table->string('prodi')->default('Teknik Informatika')->after('phone');
            }
            
            // ✅ Tambah kolom golongan jika belum ada
            if (!Schema::hasColumn('bookings', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('prodi');
            }
            
            // ✅ Tambah kolom is_group jika belum ada
            if (!Schema::hasColumn('bookings', 'is_group')) {
                $table->boolean('is_group')->default(false)->after('golongan');
            }
            
            // ✅ Tambah kolom start_date jika belum ada
            if (!Schema::hasColumn('bookings', 'start_date')) {
                $table->date('start_date')->nullable()->after('booking_date');
            }
            
            // ✅ Tambah kolom end_date jika belum ada
            if (!Schema::hasColumn('bookings', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            
            // ✅ Tambah kolom duration_days jika belum ada
            if (!Schema::hasColumn('bookings', 'duration_days')) {
                $table->integer('duration_days')->default(1)->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['phone', 'prodi', 'golongan', 'is_group', 'start_date', 'end_date', 'duration_days']);
        });
    }
};