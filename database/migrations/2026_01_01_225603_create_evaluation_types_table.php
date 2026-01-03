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
        Schema::create('evaluation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // quiz, exam, homework
            $table->string('label'); // اختبار جزئي، اختبار شامل، واجب
            $table->integer('max_score')->nullable(); // null للواجب
            $table->boolean('uses_score')->default(true);
            $table->boolean('uses_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_types');
    }
};
