<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->decimal('normal_price', 10, 2)->default(0);
            $table->decimal('peak_price', 10, 2)->default(0);
            $table->time('peak_start')->nullable();
            $table->time('peak_end')->nullable();
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Sun, 6=Sat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
