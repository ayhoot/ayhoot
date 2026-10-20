<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            // Only add columns that don't already exist
            if (!Schema::hasColumn('live_sessions', 'hide_time')) {
                $table->boolean('hide_time')->default(false)->after('config');
            }
            if (!Schema::hasColumn('live_sessions', 'ignore_time_counting')) {
                $table->boolean('ignore_time_counting')->default(false)->after('hide_time');
            }
            if (!Schema::hasColumn('live_sessions', 'teacher_paced')) {
                $table->boolean('teacher_paced')->default(false)->after('ignore_time_counting');
            }
            // show_correct_answer already exists, skip it
            if (!Schema::hasColumn('live_sessions', 'show_ranking_after_question')) {
                $table->boolean('show_ranking_after_question')->default(false)->after('show_correct_answer');
            }
        });
        
        // Update config for existing sessions with new feature flags
        DB::table('live_sessions')->update([
            'config' => DB::raw('JSON_SET(COALESCE(config, "{}"), "$.hide_time", false, "$.ignore_time_counting", false, "$.teacher_paced", false, "$.show_correct_answer", false, "$.show_ranking_after_question", false)')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            // Only drop columns that exist
            $columns = ['hide_time', 'ignore_time_counting', 'teacher_paced', 'show_ranking_after_question'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('live_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
