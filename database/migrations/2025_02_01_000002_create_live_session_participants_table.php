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
        Schema::create('live_session_participants', function (Blueprint $table) {
            $table->id();
            
            // Session reference
            $table->unsignedBigInteger('live_session_id');
            $table->foreign('live_session_id')->references('id')->on('live_sessions')->onDelete('cascade');
            
            // Student information
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            // Participant details
            $table->string('nickname')->nullable();
            $table->string('avatar')->nullable();
            
            // Status and timing
            $table->enum('status', ['joined', 'playing', 'finished', 'disconnected'])->default('joined');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            
            // Scoring
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('current_rank')->default(0);
            
            // Answer timing
            $table->unsignedInteger('total_answer_time')->default(0); // milliseconds
            $table->unsignedInteger('average_answer_time')->default(0); // milliseconds
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['live_session_id', 'student_id']);
            
            // Indexes
            $table->index(['live_session_id', 'status']);
            $table->index(['live_session_id', 'current_rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_session_participants');
    }
};
