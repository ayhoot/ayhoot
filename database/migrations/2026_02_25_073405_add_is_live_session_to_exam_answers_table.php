<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->boolean('is_live_session')->default(false)->after('id');
            $table->unsignedBigInteger('live_session_id')->nullable()->after('is_live_session');
            
            $table->foreign('live_session_id')->references('id')->on('live_sessions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['live_session_id']);
            $table->dropColumn(['is_live_session', 'live_session_id']);
        });
    }
};
