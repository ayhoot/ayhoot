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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (!Schema::hasColumn('teachers', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            }
        });

        Schema::table('institutes', function (Blueprint $table) {
            if (!Schema::hasColumn('institutes', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'city_id')) {
                $table->dropForeignIdFor('City');
                $table->dropColumn('city_id');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'city_id')) {
                $table->dropForeignIdFor('City');
                $table->dropColumn('city_id');
            }
        });

        Schema::table('institutes', function (Blueprint $table) {
            if (Schema::hasColumn('institutes', 'city_id')) {
                $table->dropForeignIdFor('City');
                $table->dropColumn('city_id');
            }
        });
    }
};
