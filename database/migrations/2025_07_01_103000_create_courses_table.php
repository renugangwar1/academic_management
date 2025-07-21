<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('course_code')->unique();
    $table->string('course_title');
    $table->enum('type', ['Theory', 'Practical']);
    $table->integer('credit_hours');
    $table->float('credit_value');
    $table->boolean('has_attendance')->default(false);
    $table->boolean('has_internal')->default(false);
    $table->boolean('has_external')->default(false);
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};


