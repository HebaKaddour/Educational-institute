<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
             $table->id();
             $table->foreignId('student_id')
        ->constrained()
        ->cascadeOnDelete();
    $table->date('start_date');
    $table->date('end_date');
    $table->decimal('monthly_fee', 8, 2);
     $table->decimal('total_fee', 8, 2);
     $table->decimal('net_fee', 8, 2);
    $table->integer('month_number');
    $table->decimal('discount_percentage', 8, 2)->default(0);
    $table->decimal('discount_amount', 8, 2)->default(0);
    $table->decimal('paid_amount', 8, 2)->default(0);
    $table->enum('status', ['ساري', 'منتهي', 'منتهي قريبا'])->default('ساري');
    $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
