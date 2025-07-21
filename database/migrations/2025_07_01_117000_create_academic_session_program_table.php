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
       Schema::create('academic_session_program', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academic_session_id')->constrained()->onDelete('cascade');
    $table->foreignId('program_id')->constrained()->onDelete('cascade');
    $table->enum('structure', ['semester', 'yearly']); // semester = regular, yearly = diploma
    $table->string('semester')->nullable(); // e.g., Sem 1, Sem 2
    $table->timestamps();

    // SHORTER NAME for the unique index
    $table->unique(
        ['academic_session_id', 'program_id', 'semester'],
        'unique_session_prog_sem'
    );
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_session_program');
    }
};
