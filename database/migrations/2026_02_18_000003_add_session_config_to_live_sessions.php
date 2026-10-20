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
        Schema::table('live_sessions', function (Blueprint $table) {
            // Session configuration that should be saved in DB
            $table->boolean('allow_late_join')->default(true)->after('settings');
            $table->boolean('show_leaderboard')->default(true)->after('allow_late_join');
            $table->boolean('randomize_questions')->default(false)->after('show_leaderboard');
            $table->boolean('randomize_options')->default(false)->after('randomize_questions');
            $table->integer('default_question_time')->default(30)->after('randomize_options'); // seconds
            
            // Real-time only configs (not saved, managed via settings JSON):
            // - auto_advance (teacher controls in real-time)
            // - current timer state (managed by current_question_started_at)
            // - pause/resume state (managed by status field)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'allow_late_join',
                'show_leaderboard',
                'randomize_questions',
                'randomize_options',
                'default_question_time',
            ]);
        });
    }
};
