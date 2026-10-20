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
        // Check if foreign key exists before trying to drop it
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'live_session_answers' 
            AND COLUMN_NAME = 'selected_option_id'
            AND CONSTRAINT_NAME LIKE '%foreign%'
        ");
        
        if (!empty($foreignKeys)) {
            Schema::table('live_session_answers', function (Blueprint $table) {
                $table->dropForeign(['selected_option_id']);
            });
        }
        
        // Change the column to string to store option IDs like "opt-0", "opt-1", etc.
        DB::statement('ALTER TABLE live_session_answers MODIFY selected_option_id VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to unsignedBigInteger
        DB::statement('ALTER TABLE live_session_answers MODIFY selected_option_id BIGINT UNSIGNED NULL');
    }
};
