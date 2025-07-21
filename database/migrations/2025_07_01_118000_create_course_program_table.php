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
       Schema::create('course_program', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->unsignedBigInteger('program_id');
    $table->integer('semester')->nullable();
    $table->integer('year')->nullable();
    $table->timestamps();

    $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
    $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');

    // Allow same course in multiple semesters
    $table->unique(['course_id', 'program_id', 'semester', 'year']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_program');
    }
};
