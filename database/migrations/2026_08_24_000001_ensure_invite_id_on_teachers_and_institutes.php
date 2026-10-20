<?php

use App\Models\Institute;
use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure every existing teacher has a valid invite_id
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'invite_id')) {
            Teacher::whereNull('invite_id')->orWhere('invite_id', '')->each(function ($teacher) {
                $teacher->updateQuietly(['invite_id' => (string) Str::uuid()]);
            });
        }

        // Ensure every existing institute has a valid invite_id
        if (Schema::hasTable('institutes') && Schema::hasColumn('institutes', 'invite_id')) {
            Institute::whereNull('invite_id')->orWhere('invite_id', '')->each(function ($institute) {
                $institute->updateQuietly(['invite_id' => (string) Str::uuid()]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: preserving generated UUIDs
    }
};
