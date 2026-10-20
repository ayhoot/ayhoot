<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'banner_settings',
        'discount_settings',
        'payment_settings',
        'general_settings',
        'description',
        'is_active',
    ];

    protected $casts = [
        'banner_settings' => 'array',
        'discount_settings' => 'array',
        'payment_settings' => 'array',
        'general_settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get discount settings
     */
    public static function getDiscountSettings()
    {
        $setting = static::first();
        return $setting ? $setting->discount_settings : [];
    }

    /**
     * Get banner settings
     */
    public static function getBannerSettings()
    {
        $setting = static::first();
        return $setting ? $setting->banner_settings : [];
    }

    /**
     * Get general settings
     */
    public static function getGeneralSettings()
    {
        $setting = static::where('key', 'general_config')->first();
        return $setting ? $setting->general_settings : [];
    }

    /**
     * Check if locale filtering is enabled
     */
    public static function isLocaleFilteringEnabled()
    {
        $generalSettings = static::getGeneralSettings();
        return $generalSettings['locale_filtering_enabled'] ?? false;
    }

    /**
     * Get supported locales
     */
    public static function getSupportedLocales()
    {
        $generalSettings = static::getGeneralSettings();
        return $generalSettings['supported_locales'] ?? ['en'];
    }

    /**
     * Get default locale
     */
    public static function getDefaultLocale()
    {
        $generalSettings = static::getGeneralSettings();
        return $generalSettings['default_locale'] ?? 'en';
    }
}