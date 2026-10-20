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
        Schema::table('settings', function (Blueprint $table) {
            // Add the JSON columns that the Setting model expects
            $table->json('banner_settings')->nullable()->after('value');
            $table->json('discount_settings')->nullable()->after('banner_settings');
            $table->json('payment_settings')->nullable()->after('discount_settings');
            $table->json('general_settings')->nullable()->after('payment_settings');
            $table->boolean('is_active')->default(true)->after('general_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'banner_settings',
                'discount_settings', 
                'payment_settings',
                'general_settings',
                'is_active'
            ]);
        });
    }
};