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
      Schema::create('results_summaries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('program_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('semester');
    $table->decimal('sgpa', 4, 2);
    $table->smallInteger('cumulative_credits');
    $table->decimal('cgpa', 4, 2);
       $table->text('failing_course_ids')->nullable();
            $table->text('failing_course_codes')->nullable();
            $table->text('failing_courses')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results_summaries');
    }
};
