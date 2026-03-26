<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom is_kalab setelah kolom role
            $table->boolean('is_kalab')->default(false)->after('role');

            // Optional: tambah index untuk performa
            $table->index('is_kalab');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_kalab']);
            $table->dropColumn('is_kalab');
        });
    }
};
