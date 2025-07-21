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
       Schema::create('grading_scales', function (Blueprint $table) {
    $table->id();
    $table->string('grade');
    $table->decimal('min_percentage', 5, 2);
    $table->decimal('max_percentage', 5, 2);
    $table->decimal('point', 4, 2);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_scales');
    }
};
