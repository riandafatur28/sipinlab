<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'lab_name')) {
                $table->string('lab_name')->nullable()->after('prodi');
                $table->index('lab_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lab_name')) {
                $table->dropIndex(['lab_name']);
                $table->dropColumn('lab_name');
            }
        });
    }
};
