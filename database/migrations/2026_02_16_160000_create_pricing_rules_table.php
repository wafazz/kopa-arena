<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('day_of_week')->nullable();
            $table->decimal('normal_price', 10, 2);
            $table->decimal('peak_price', 10, 2)->nullable();
            $table->time('peak_start')->nullable();
            $table->time('peak_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
