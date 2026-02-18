<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_time_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('slot_duration')->default(90);
            $table->integer('slot_interval')->default(30);
            $table->time('earliest_start')->default('08:00');
            $table->time('latest_start')->default('22:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_time_rules');
    }
};
