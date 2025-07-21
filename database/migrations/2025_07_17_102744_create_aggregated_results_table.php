<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aggregated_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('semester');
            $table->unsignedTinyInteger('year')->nullable(); // optional, for reporting

            // Marks in string format (course_code:mark)
            $table->text('internal_marks')->nullable();        // BHA101:20,BHA102:30
            $table->text('internal_reappear')->nullable();     // BHA101,BHA103
            $table->text('external_marks')->nullable();        // BHA101:45,BHA102:50
            $table->text('external_reappear')->nullable();     // BHA102
            $table->text('attendance_marks')->nullable();      // BHA101:5,...
            $table->text('total_marks')->nullable();           // BHA101:70,...

            // Results
            $table->text('final_result')->nullable();          // PASS, FAIL
            $table->tinyInteger('total_reappear_subjects')->default(0);
            $table->text('remarks')->nullable();               // Optional: promotion, improvement, etc.

            // Academic performance
            $table->decimal('sgpa', 4, 2)->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->unsignedSmallInteger('cumulative_credits')->default(0);

            // For record traceability
            $table->tinyInteger('exam_attempt')->default(1);  // 1 = regular, 2 = 1st reappear, etc.
            $table->timestamp('compiled_at')->nullable();     // when results were compiled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aggregated_results');
    }
};
