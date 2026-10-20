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
        Schema::table('courses', function (Blueprint $table) {
            // Add folder_id column as a foreign key, nullable, and on delete set null
            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropConstrainedForeignId('folder_id');
        });
    }
};
