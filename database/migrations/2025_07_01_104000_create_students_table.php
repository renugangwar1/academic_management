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
        // 1) Drop the old table (if it exists)
     Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('nchm_roll_number')->unique();
    $table->string('enrolment_number')->nullable();

    $table->foreignId('program_id')->constrained()->cascadeOnDelete();
    $table->foreignId('institute_id')->constrained()->cascadeOnDelete();

    $table->unsignedTinyInteger('semester')->nullable();
    $table->unsignedTinyInteger('year')->nullable();

    // 🔧 Fix: Ensure this is defined as unsigned big integer (default of foreignId)
    $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();

    $table->string('email')->nullable();
    $table->string('mobile')->nullable();
    $table->date('date_of_birth')->nullable();
    $table->string('category')->nullable();
    $table->string('father_name')->nullable();

    $table->boolean('status')->default(true);
    $table->timestamps();

    $table->index(['program_id', 'semester']);
    $table->index(['program_id', 'year']);
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