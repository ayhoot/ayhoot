<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the general_config setting to include locale settings
        $generalSetting = Setting::where('key', 'general_config')->first();
        
        if ($generalSetting) {
            $generalSettings = $generalSetting->general_settings ?? [];
            
            // Add locale settings to existing general settings
            $generalSettings['locale_filtering_enabled'] = true;
            $generalSettings['supported_locales'] = ['en', 'fa'];
            $generalSettings['default_locale'] = 'en';
            
            $generalSetting->update([
                'general_settings' => $generalSettings
            ]);
        } else {
            // Create general_config if it doesn't exist
            Setting::create([
                'key' => 'general_config',
                'value' => 'General platform settings',
                'description' => 'General configuration for the platform',
                'general_settings' => [
                    'platform_name' => 'Ayhoot',
                    'support_email' => 'support@ayhoot.com',
                    'max_discount_percentage' => 50,
                    'min_purchase_amount' => 1000,
                    'locale_filtering_enabled' => true,
                    'supported_locales' => ['en', 'fa'],
                    'default_locale' => 'en',
                ],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $generalSetting = Setting::where('key', 'general_config')->first();
        
        if ($generalSetting) {
            $generalSettings = $generalSetting->general_settings ?? [];
            
            // Remove locale settings
            unset($generalSettings['locale_filtering_enabled']);
            unset($generalSettings['supported_locales']);
            unset($generalSettings['default_locale']);
            
            $generalSetting->update([
                'general_settings' => $generalSettings
            ]);
        }
    }
};