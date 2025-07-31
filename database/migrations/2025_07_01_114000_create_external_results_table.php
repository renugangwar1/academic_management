<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('semester');
$table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();

            $table->smallInteger('internal')->default(0);
            $table->smallInteger('external')->default(0);
                $table->smallInteger('attendance')->default(0);
            $table->smallInteger('total')->default(0);
            $table->tinyInteger('credit')->default(0);
            $table->decimal('grade_point', 4, 2)->default(0.00);

            // Additional fields
            $table->decimal('sgpa', 4, 2)->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->string('grade_letter', 2)->nullable();   // e.g., A+, B, F
            $table->string('result_status', 10)->nullable(); // e.g., PASS, FAIL
            $table->tinyInteger('exam_attempt')->default(1); // for repeat attempts

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_results');
    }
};
