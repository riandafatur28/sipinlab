<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambah kolom yang mungkin missing
            if (!Schema::hasColumn('bookings', 'phone')) {
                $table->string('phone', 20)->nullable()->after('purpose');
            }
            if (!Schema::hasColumn('bookings', 'prodi')) {
                $table->string('prodi')->default('Teknik Informatika')->after('phone');
            }
            if (!Schema::hasColumn('bookings', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('prodi');
            }
            if (!Schema::hasColumn('bookings', 'is_group')) {
                $table->boolean('is_group')->default(false)->after('golongan');
            }
            if (!Schema::hasColumn('bookings', 'members')) {
                $table->json('members')->nullable()->after('is_group');
            }
            if (!Schema::hasColumn('bookings', 'approved_by_teknisi')) {
                $table->foreignId('approved_by_teknisi')->nullable()->constrained('users')->nullOnDelete()->after('approved_at_dosen');
            }
            if (!Schema::hasColumn('bookings', 'approved_at_teknisi')) {
                $table->timestamp('approved_at_teknisi')->nullable()->after('approved_by_teknisi');
            }
            if (!Schema::hasColumn('bookings', 'approved_by_kalab')) {
                $table->foreignId('approved_by_kalab')->nullable()->constrained('users')->nullOnDelete()->after('approved_at_teknisi');
            }
            if (!Schema::hasColumn('bookings', 'approved_at_kalab')) {
                $table->timestamp('approved_at_kalab')->nullable()->after('approved_by_kalab');
            }
            
            // Pastikan start_time dan end_time nullable
            if (Schema::hasColumn('bookings', 'start_time')) {
                $table->time('start_time')->nullable()->change();
            }
            if (Schema::hasColumn('bookings', 'end_time')) {
                $table->time('end_time')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Optional
    }
};