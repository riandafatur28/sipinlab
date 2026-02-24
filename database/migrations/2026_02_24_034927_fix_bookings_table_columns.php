<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tambah kolom phone jika belum ada
            if (!Schema::hasColumn('bookings', 'phone')) {
                $table->string('phone', 20)->nullable()->after('purpose');
            }
            
            // Tambah kolom prodi jika belum ada
            if (!Schema::hasColumn('bookings', 'prodi')) {
                $table->string('prodi')->default('Teknik Informatika')->after('phone');
            }
            
            // Tambah kolom golongan jika belum ada
            if (!Schema::hasColumn('bookings', 'golongan')) {
                $table->string('golongan', 10)->nullable()->after('prodi');
            }
            
            // Tambah kolom is_group jika belum ada
            if (!Schema::hasColumn('bookings', 'is_group')) {
                $table->boolean('is_group')->default(false)->after('golongan');
            }
            
            // Tambah kolom members jika belum ada
            if (!Schema::hasColumn('bookings', 'members')) {
                $table->json('members')->nullable()->after('is_group');
            }
            
            // Tambah kolom supervisor_id jika belum ada
            if (!Schema::hasColumn('bookings', 'supervisor_id')) {
                $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete()->after('members');
            }
            
            // Tambah kolom status jika belum ada
            if (!Schema::hasColumn('bookings', 'status')) {
                $table->enum('status', ['pending', 'approved_dosen', 'approved_teknisi', 'confirmed', 'rejected', 'cancelled'])->default('pending')->after('supervisor_id');
            }
            
            // Tambah kolom approval dosen jika belum ada
            if (!Schema::hasColumn('bookings', 'approved_by_dosen')) {
                $table->foreignId('approved_by_dosen')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
            if (!Schema::hasColumn('bookings', 'approved_at_dosen')) {
                $table->timestamp('approved_at_dosen')->nullable()->after('approved_by_dosen');
            }
            
            // Tambah kolom approval teknisi jika belum ada
            if (!Schema::hasColumn('bookings', 'approved_by_teknisi')) {
                $table->foreignId('approved_by_teknisi')->nullable()->constrained('users')->nullOnDelete()->after('approved_at_dosen');
            }
            if (!Schema::hasColumn('bookings', 'approved_at_teknisi')) {
                $table->timestamp('approved_at_teknisi')->nullable()->after('approved_by_teknisi');
            }
            
            // Tambah kolom approval ka lab jika belum ada
            if (!Schema::hasColumn('bookings', 'approved_by_kalab')) {
                $table->foreignId('approved_by_kalab')->nullable()->constrained('users')->nullOnDelete()->after('approved_at_teknisi');
            }
            if (!Schema::hasColumn('bookings', 'approved_at_kalab')) {
                $table->timestamp('approved_at_kalab')->nullable()->after('approved_by_kalab');
            }
            
            // Tambah kolom rejection jika belum ada
            if (!Schema::hasColumn('bookings', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at_kalab');
            }
            if (!Schema::hasColumn('bookings', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('bookings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            
            // Tambah kolom notes jika belum ada
            if (!Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable()->after('rejection_reason');
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
        // Optional: tidak perlu drop jika kolom memang diperlukan
    }
};