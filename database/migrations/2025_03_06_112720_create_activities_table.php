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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id')->nullable(); 
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('set null');
            $table->unsignedBigInteger('student_id')->nullable(); 
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
            $table->unsignedBigInteger('admin_id')->nullable(); 
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->unsignedBigInteger('institute_id')->nullable(); 
            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('set null');
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
