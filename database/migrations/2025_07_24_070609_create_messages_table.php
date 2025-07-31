<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('institute_id');
        $table->unsignedBigInteger('admin_id');  // just a column, no FK constraint
           $table->string('subject')->nullable();
        $table->text('message');
            $table->boolean('is_admin')->default(false); 
        $table->boolean('is_read')->default(false);
        $table->timestamps();

        $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
        // remove foreign key constraint on admin_id for now
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
