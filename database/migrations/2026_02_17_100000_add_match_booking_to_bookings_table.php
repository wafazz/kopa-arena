<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('booking_type', ['normal', 'match'])->default('normal')->after('status');
            $table->foreignId('match_parent_id')->nullable()->after('booking_type')
                ->constrained('bookings')->nullOnDelete();

            // Must drop FK first, then unique index, then re-add FK
            $table->dropForeign(['facility_id']);
            $table->dropUnique(['facility_id', 'booking_date', 'start_time']);
            $table->foreign('facility_id')->references('id')->on('facilities')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['match_parent_id']);
            $table->dropColumn(['booking_type', 'match_parent_id']);
            $table->dropForeign(['facility_id']);
            $table->unique(['facility_id', 'booking_date', 'start_time']);
            $table->foreign('facility_id')->references('id')->on('facilities')->cascadeOnDelete();
        });
    }
};
