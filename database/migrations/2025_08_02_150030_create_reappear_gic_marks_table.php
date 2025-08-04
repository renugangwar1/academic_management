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
        Schema::create('reappear_gic_marks', function (Blueprint $table) {
            $table->id();

            // Core student data
            $table->unsignedBigInteger('student_id');
            $table->string('roll_number');
            $table->string('student_name');

            // Academic & Program context
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('semester')->nullable();
            $table->unsignedTinyInteger('year')->nullable();

            // Course/reappear info
            $table->string('course_code');
            $table->string('course_name');
            $table->string('reappear_type'); // regular, improvement, etc.
            $table->decimal('gic_marks', 5, 2)->nullable();

            $table->timestamps();

            // Indexes for quick filtering
            $table->index(['academic_session_id', 'program_id']);
            $table->index(['institute_id', 'semester', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reappear_gic_marks');
    }
};
