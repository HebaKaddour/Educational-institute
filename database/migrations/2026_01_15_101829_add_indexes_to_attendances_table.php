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
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('date');
            $table->index('status');

        // composite
        $table->index(['date', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
        $table->dropIndex(['date']);
        $table->dropIndex(['status']);
        $table->dropIndex(['date', 'student_id']);
        });
    }
};
