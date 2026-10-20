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
        // First, add answer_text column
        if (!Schema::hasColumn('live_session_answers', 'answer_text')) {
            Schema::table('live_session_answers', function (Blueprint $table) {
                $table->text('answer_text')->nullable()->after('selected_option_id');
            });
        }
        
        // Migrate existing data: move text answers to answer_text column
        DB::statement("
            UPDATE live_session_answers 
            SET answer_text = selected_option_id,
                selected_option_id = NULL
            WHERE selected_option_id IS NOT NULL 
            AND selected_option_id NOT REGEXP '^[0-9]+$'
        ");
        
        // Now modify the column type using raw SQL to avoid foreign key issues
        DB::statement('ALTER TABLE live_session_answers MODIFY selected_option_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('live_session_answers', 'answer_text')) {
            Schema::table('live_session_answers', function (Blueprint $table) {
                $table->dropColumn('answer_text');
            });
        }
    }
};
