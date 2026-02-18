<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('close_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('close_date');
            $table->foreignId('closed_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->unsignedInteger('total_bookings')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'close_date']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });

        Schema::dropIfExists('close_sales');
    }
};
