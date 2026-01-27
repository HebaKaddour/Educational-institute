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
        Schema::create('students', function (Blueprint $table) {
         $table->id();
          $table->string('full_name');
          $table->bigInteger('identification_number')->unique();
          $table->string('student_mobile')->nullable();
          $table->string('guardian_mobile')->nullable();
          $table->integer('age');
          $table->string('section');
           $table->enum('gender', ['ذكر','انثى']);
          $table->string('school');
            $table->enum('status', ['نشط', 'منسحب'])
        ->default('نشط');
         $table->string('grade')->nullable(); //الصف

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
