<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instructors (teachers + institutes) can save a bank card / SHABA number for
 * wallet withdrawals. The wallet "Add SHABA" action writes $instructor->card,
 * but the column was missing — adding it here.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['teachers', 'institutes'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'card')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('card')->nullable()->after('wallet');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['teachers', 'institutes'] as $table) {
            if (Schema::hasColumn($table, 'card')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('card');
                });
            }
        }
    }
};
