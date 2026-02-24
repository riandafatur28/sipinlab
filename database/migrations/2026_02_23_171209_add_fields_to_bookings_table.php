<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('activity')->nullable()->after('purpose');
            $table->json('members')->nullable()->after('activity');
            $table->foreignId('supervisor_id')->nullable()->after('members')->constrained('users')->onDelete('set null');
            $table->integer('duration_days')->default(1)->after('supervisor_id');
            $table->date('start_date')->nullable()->after('duration_days');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['activity', 'members', 'supervisor_id', 'duration_days', 'start_date', 'end_date']);
        });
    }
};
