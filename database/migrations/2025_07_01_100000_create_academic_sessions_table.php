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
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('year');                     // e.g., 2023‑2024
            $table->enum('term', ['Jan', 'July']);      // Jan | July
           $table->enum('odd_even', ['odd', 'even'])->nullable();  // odd | even
            $table->boolean('active')->default(false);
            $table->string('type')->nullable();         // regular | diploma | ...
            $table->enum('diploma_year', ['1', '2'])->nullable(); // 1 = first year, 2 = second year
            $table->timestamps();

            // Unique combination of year, term, type, and diploma_year
            $table->unique(['year', 'term', 'type', 'diploma_year'], 'unique_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};
