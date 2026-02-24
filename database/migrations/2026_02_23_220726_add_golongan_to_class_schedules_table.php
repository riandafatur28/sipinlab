<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->string('golongan')->nullable()->after('class_name'); // A, B, C
        });
    }

    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropColumn('golongan');
        });
    }
};