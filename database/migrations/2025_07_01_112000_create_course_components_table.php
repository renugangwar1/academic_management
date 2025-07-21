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
      Schema::create('course_components', function (Blueprint $table) {
    $table->id();
    $table->foreignId('course_id')->constrained()->onDelete('cascade');
  $table->decimal('internal_max', 5, 2)->nullable();
$table->decimal('internal_min', 5, 2)->nullable();
$table->decimal('external_max', 5, 2)->nullable();
$table->decimal('external_min', 5, 2)->nullable();
$table->decimal('attendance_max', 5, 2)->nullable();
$table->decimal('attendance_min', 5, 2)->nullable();

$table->string('total_from')->nullable();
$table->decimal('total_marks', 6, 2)->nullable();         
$table->decimal('min_passing_marks', 6, 2)->nullable(); 
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_components');
    }
};
