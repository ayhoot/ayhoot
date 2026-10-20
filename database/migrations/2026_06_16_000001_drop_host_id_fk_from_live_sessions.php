<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * live_sessions.host_id is polymorphic (host_type = Teacher | Institute), but the
 * original migration added a foreign key to `teachers` only, so institute hosts
 * violated the constraint. Drop the FK; integrity is enforced in the app layer
 * via host_id + host_type (which already has a composite index).
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'live_sessions')
            ->where('CONSTRAINT_NAME', 'live_sessions_host_id_foreign')
            ->exists();

        if ($exists) {
            Schema::table('live_sessions', function (Blueprint $table) {
                $table->dropForeign('live_sessions_host_id_foreign');
            });
        }
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->foreign('host_id')->references('id')->on('teachers')->onDelete('cascade');
        });
    }
};
