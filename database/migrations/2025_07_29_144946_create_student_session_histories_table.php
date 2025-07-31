<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('student_session_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
$table->unsignedTinyInteger('from_semester')->nullable();
$table->unsignedTinyInteger('to_semester');
            $table->unsignedTinyInteger('semester');
            $table->string('promotion_type')->nullable(); // e.g. passed, failed, manual, initial
            $table->timestamp('promoted_at')->nullable(); // null for initial entries
            $table->unsignedBigInteger('promoted_by')->nullable(); // Admin user or system

            $table->timestamps();

            // 🔧 Custom index name
            $table->index(['student_id', 'academic_session_id', 'semester'], 'student_session_index');
        });
    }

    public function down(): void {
        Schema::dropIfExists('student_session_histories');
    }
};
