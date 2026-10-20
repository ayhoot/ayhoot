<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('games')) {
            try {
                DB::statement("ALTER TABLE `games` MODIFY COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'active'");
            } catch (\Throwable $e) {
                try {
                    Schema::table('games', function (Blueprint $table) {
                        $table->string('status', 32)->default('active')->change();
                    });
                } catch (\Throwable $ignored) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('games')) {
            try {
                DB::statement("ALTER TABLE `games` MODIFY COLUMN `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
            } catch (\Throwable $e) {}
        }
    }
};
