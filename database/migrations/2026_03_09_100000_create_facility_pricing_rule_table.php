<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_pricing_rule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->onDelete('cascade');
            $table->foreignId('pricing_rule_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['facility_id', 'pricing_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_pricing_rule');
    }
};
