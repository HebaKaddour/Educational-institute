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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
    $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('evaluation_type_id')->constrained()->cascadeOnDelete();

    $table->integer('score')->nullable();
    $table->enum('status', ['completed', 'not_completed'])->nullable();

    $table->unsignedTinyInteger('week')->nullable();
    $table->date('date');

    $table->timestamps();

    $table->unique([
        'student_id',
        'subject_id',
        'evaluation_type_id',
        'week',
        'date'
    ], 'unique_student_evaluation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
