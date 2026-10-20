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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
            $table->unsignedBigInteger('institute_id')->nullable();
            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('cascade');
            $table->text('title');
            $table->enum('type', ['true_false', 'multiple_choice', 'ordinary', 'sequence', 'textarea', 'slide'])->default('multiple_choice');
            $table->integer('duration')->default(30);
            $table->integer('points')->default(10);
            $table->string('correct_answer')->nullable();
            $table->json('options')->nullable();
            $table->string('short_answer')->nullable();
            $table->json('alternative_answers')->nullable();
            $table->json('sequence_options')->nullable();
            $table->boolean('allow_partial_credit')->default(false);
            $table->text('long_answer')->nullable();
            $table->text('slide')->nullable();
            $table->text('explanation')->nullable();
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('category_id')->nullable();
            $table->json('files')->nullable();
            $table->json('q_options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
