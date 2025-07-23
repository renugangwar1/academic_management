<?php
// database/migrations/xxxx_xx_xx_create_student_uploads_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentUploadsTable extends Migration
{
    public function up()
    {
        Schema::create('student_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institute_id');
            $table->string('filename');
               $table->string('file_path')->nullable();
             $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_uploads');
    }
};