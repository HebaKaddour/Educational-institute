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
    // نوع التقييم مباشرة كـ Enum value
    $table->string('evaluation_type');
    // عدد الواجبات (للتقييمات التي تعتمد على عدد مرات الإنجاز)
    $table->unsignedSmallInteger('max_count')->nullable();
    // الدرجة القصوى للاختبارات
    $table->unsignedSmallInteger('max_score')->nullable();

    $table->timestamps();

    $table->unique(['subject_id', 'evaluation_type'], 'unique_subject_eval_type');
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
