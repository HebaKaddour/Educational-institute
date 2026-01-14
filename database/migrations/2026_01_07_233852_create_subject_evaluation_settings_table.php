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
        Schema::create('subject_evaluation_settings', function (Blueprint $table) {
            $table->id();
             $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('evaluation_type_id')->constrained()->cascadeOnDelete();

    $table->unsignedSmallInteger('max_score');
    $table->unsignedSmallInteger('max_count')->nullable();

    $table->timestamps();

    $table->unique(['subject_id', 'evaluation_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_evaluation_settings');
    }
};
