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
        if (!Schema::hasTable('games') || Schema::hasColumn('games', 'live_session_id')) {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('live_session_id')->nullable()->after('id');
            $table->foreign('live_session_id')->references('id')->on('live_sessions')->onDelete('cascade');
            $table->index('live_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('games') || !Schema::hasColumn('games', 'live_session_id')) {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['live_session_id']);
            $table->dropIndex(['live_session_id']);
            $table->dropColumn('live_session_id');
        });
    }
};
