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
        // Add locale column to folders table if it doesn't exist
        if (!Schema::hasColumn('folders', 'locale')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->string('locale', 5)->default('en')->after('teacher_id');
                $table->index('locale');
            });
        }

        // Add locale column to groups table if it doesn't exist
        if (!Schema::hasColumn('groups', 'locale')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->string('locale', 5)->default('en')->after('description');
                $table->index('locale');
            });
        }

        // Add locale column to questions table if it doesn't exist
        if (!Schema::hasColumn('questions', 'locale')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->string('locale', 5)->default('en')->after('exam_id');
                $table->index('locale');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('folders', 'locale')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropIndex(['locale']);
                $table->dropColumn('locale');
            });
        }

        if (Schema::hasColumn('groups', 'locale')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropIndex(['locale']);
                $table->dropColumn('locale');
            });
        }

        if (Schema::hasColumn('questions', 'locale')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropIndex(['locale']);
                $table->dropColumn('locale');
            });
        }
    }
};