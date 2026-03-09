<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Migrate existing branch_pricing_rule data to facility-level
        $rows = DB::table('branch_pricing_rule')->get();
        foreach ($rows as $row) {
            $facilityIds = DB::table('facilities')->where('branch_id', $row->branch_id)->pluck('id');
            foreach ($facilityIds as $fid) {
                DB::table('facility_pricing_rule')->insertOrIgnore([
                    'facility_id' => $fid,
                    'pricing_rule_id' => $row->pricing_rule_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_pricing_rule');
    }
};
