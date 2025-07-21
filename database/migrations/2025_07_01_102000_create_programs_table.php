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
    Schema::create('programs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->integer('duration'); // numerical value
    $table->enum('duration_unit', ['year', 'month', 'day']); // added
    $table->enum('structure', ['semester', 'yearly', 'short_term']); // added
    $table->timestamps();

});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
