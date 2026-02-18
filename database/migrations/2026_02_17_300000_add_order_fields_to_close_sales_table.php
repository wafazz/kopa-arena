<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('close_sales', function (Blueprint $table) {
            $table->unsignedInteger('total_orders')->default(0)->after('total_bookings');
            $table->decimal('total_order_amount', 12, 2)->default(0)->after('total_orders');
        });
    }

    public function down(): void
    {
        Schema::table('close_sales', function (Blueprint $table) {
            $table->dropColumn(['total_orders', 'total_order_amount']);
        });
    }
};
