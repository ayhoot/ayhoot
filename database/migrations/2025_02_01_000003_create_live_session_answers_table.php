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
        Schema::create('live_session_answers', function (Blueprint $table) {
            $table->id();
            
            // Session and participant
            $table->unsignedBigInteger('live_session_id');
            $table->foreign('live_session_id')->references('id')->on('live_sessions')->onDelete('cascade');
            
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            // Question information
            $table->unsignedBigInteger('question_id');
            $table->unsignedInteger('question_index'); // Order in the exam
            
            // Answer details
            $table->unsignedBigInteger('selected_option_id')->nullable();
            
            // Timing
            $table->timestamp('answered_at')->nullable();
            $table->unsignedInteger('answer_time_ms')->default(0); // Time taken to answer
            $table->unsignedInteger('time_remaining_ms')->default(0); // Time left when answered
            
            // Scoring
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedInteger('current_rank_after_answer')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['live_session_id', 'student_id']);
            $table->index(['live_session_id', 'question_index']);
            $table->index(['live_session_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_session_answers');
    }
};
