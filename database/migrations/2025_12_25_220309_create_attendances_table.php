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
        Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained()->cascadeOnDelete();
        $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
        $table->unsignedTinyInteger('week'); // 36 week 1 → 36
        $table->enum('day', [
        'السبت',
        'الأحد',
        'الاثنين',
        'الثلاثاء',
        'الأربعاء',
        'الخميس'
         ]);

        $table->enum('status', ['حضور', 'غياب']);

         $table->timestamps();
    // منع تكرار تسجيل نفس الطالب لنفس المادة في نفس اليوم من نفس الأسبوع
    $table->unique(['student_id', 'subject_id', 'week', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
