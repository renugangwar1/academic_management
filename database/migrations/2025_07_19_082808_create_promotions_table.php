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
       Schema::create('promotions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedTinyInteger('from_semester');
    $table->unsignedTinyInteger('to_semester');
    $table->unsignedBigInteger('promoted_by'); // Admin/Staff user_id
    $table->timestamp('promoted_at')->useCurrent();
    $table->timestamps();

    $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
