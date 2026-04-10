<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slot_time_rules', function (Blueprint $table) {
            $table->json('interval_overrides')->nullable()->after('slot_interval');
        });
    }

    public function down(): void
    {
        Schema::table('slot_time_rules', function (Blueprint $table) {
            $table->dropColumn('interval_overrides');
        });
    }
};
