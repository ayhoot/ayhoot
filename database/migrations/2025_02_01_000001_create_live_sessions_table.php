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
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('pin', 6)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Host information
            $table->unsignedBigInteger('host_id');
            $table->string('host_type'); // teacher, institute
            $table->foreign('host_id')->references('id')->on('teachers')->onDelete('cascade');
            
            // Exam information
            $table->unsignedBigInteger('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            
            // Session status
            $table->enum('status', ['waiting', 'started', 'question', 'finished'])->default('waiting');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            
            // Current question tracking
            $table->unsignedInteger('current_question_index')->default(0);
            $table->unsignedInteger('current_question_time_limit')->default(30); // seconds
            $table->timestamp('current_question_started_at')->nullable();
            
            // Session settings
            $table->boolean('show_ranking_after_each_question')->default(true);
            $table->boolean('show_correct_answer')->default(true);
            $table->json('settings')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['pin', 'status']);
            $table->index(['host_id', 'host_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
