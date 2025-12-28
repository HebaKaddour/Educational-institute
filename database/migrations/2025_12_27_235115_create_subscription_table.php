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
        ->cascadeOnDelete()->unique();;

    $table->foreignId('subject_id')
        ->constrained()
        ->cascadeOnDelete()->unique();
        $table->date('start_date');
    $table->date('end_date');

    $table->decimal('fee', 8, 2);
    $table->decimal('discount', 8, 2)->default(0);
    $table->decimal('paid_amount', 8, 2)->default(0);
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
