<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // ✅ Ubah start_time menjadi nullable
            $table->time('start_time')->nullable()->change();
            
            // ✅ Ubah end_time menjadi nullable
            $table->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Kembalikan ke NOT NULL (dengan default value untuk menghindari error)
            $table->time('start_time')->default('00:00:00')->change();
            $table->time('end_time')->default('00:00:00')->change();
        });
    }
};