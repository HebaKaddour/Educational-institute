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
        $table->string('evaluation_type'); // Enum value
    $table->date('evaluation_date');
    $table->unsignedSmallInteger('evaluation_number')->nullable();

    // الدرجة لكل تقييم (اختبارات، الحضور، المشاركة)
    $table->integer('score')->nullable();

    // الواجبات فقط
    $table->boolean('is_completed')->nullable();

    $table->timestamps();

    $table->unique([
        'student_id',
        'subject_id',
        'evaluation_type',
        'evaluation_date',
        'evaluation_number',
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
