<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('checkin_token', 64)->nullable()->unique()->after('transaction_id');
            $table->timestamp('checked_in_at')->nullable()->after('checkin_token');
            $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')->constrained('users')->nullOnDelete();
        });

        // Backfill existing bookings with tokens
        $bookings = \App\Models\Booking::withTrashed()->whereNull('checkin_token')->get();
        foreach ($bookings as $booking) {
            $booking->update(['checkin_token' => Str::random(40)]);
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['checked_in_by']);
            $table->dropColumn(['checkin_token', 'checked_in_at', 'checked_in_by']);
        });
    }
};
