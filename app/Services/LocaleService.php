<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;

class LocaleService
{
    /**
     * Check if locale filtering is enabled
     */
    public static function isLocaleFilteringEnabled(): bool
    {
        $settings = Setting::where('key', 'general_config')->first();
        $generalSettings = $settings->general_settings ?? [];
        
        return $generalSettings['locale_filtering_enabled'] ?? false;
    }

    /**
     * Get supported locales
     */
    public static function getSupportedLocales(): array
    {
        $settings = Setting::where('key', 'general_config')->first();
        $generalSettings = $settings->general_settings ?? [];
        
        return $generalSettings['supported_locales'] ?? ['en'];
    }

    /**
     * Get default locale
     */
    public static function getDefaultLocale(): string
    {
        $settings = Setting::where('key', 'general_config')->first();
        $generalSettings = $settings->general_settings ?? [];
        
        return $generalSettings['default_locale'] ?? 'en';
    }

    /**
     * Get current user's locale from request headers
     */
    public static function getCurrentLocale(): string
    {
        // Check Accept-Language header or cookie
        $header = request()->header('Accept-Language') ?? request()->cookie('lang');
        if ($header) {
            $parsed = self::parseAcceptLanguage($header);
            if (in_array($parsed, ['en', 'fa'])) {
                return $parsed;
            }
        }

        return self::getDefaultLocale();
    }

    /**
     * Parse Accept-Language header to get primary locale
     */
    private static function parseAcceptLanguage(string $acceptLanguage): string
    {
        // Split by comma and get the first (highest priority) language
        $languages = explode(',', $acceptLanguage);
        $primaryLanguage = trim($languages[0]);
        
        // Remove quality factor if present (e.g., "en-US;q=0.9" -> "en-US")
        $primaryLanguage = explode(';', $primaryLanguage)[0];
        
        // Extract the primary locale (e.g., 'fa' from 'fa-IR' or 'en' from 'en-US')
        return substr(trim($primaryLanguage), 0, 2);
    }

    /**
     * Apply locale filtering to a query builder
     */
    public static function applyLocaleFilter(Builder $query, ?string $locale = null): Builder
    {
        if (!self::isLocaleFilteringEnabled()) {
            return $query;
        }

        $locale = $locale ?? self::getCurrentLocale();
        
        return $query->where('locale', $locale);
    }

    /**
     * Get locale for storing new content
     */
    public static function getStoreLocale(): string
    {
        if (!self::isLocaleFilteringEnabled()) {
            return self::getDefaultLocale();
        }

        return self::getCurrentLocale();
    }

    /**
     * Add locale scope to models
     */
    public static function addLocaleScopes()
    {
        // This method can be called in a service provider to add global scopes
        if (self::isLocaleFilteringEnabled()) {
            \App\Models\Course::addGlobalScope('locale', function (Builder $builder) {
                $builder->where('locale', self::getCurrentLocale());
            });

            \App\Models\Exam::addGlobalScope('locale', function (Builder $builder) {
                $builder->where('locale', self::getCurrentLocale());
            });

            \App\Models\Folder::addGlobalScope('locale', function (Builder $builder) {
                $builder->where('locale', self::getCurrentLocale());
            });

            \App\Models\Group::addGlobalScope('locale', function (Builder $builder) {
                $builder->where('locale', self::getCurrentLocale());
            });

            \App\Models\Question::addGlobalScope('locale', function (Builder $builder) {
                $builder->where('locale', self::getCurrentLocale());
            });
        }
    }

    /**
     * Clear all locale-related global scopes
     */
    public static function clearLocaleScopes()
    {
        // Global scopes are automatically cleared when new ones are added with same name
        // No need to manually remove them
    }

    /**
     * Refresh locale scopes with new locale
     */
    public static function refreshLocaleScopes(?string $newLocale = null)
    {
        // Don't use global scopes - they cause issues
        // Instead, apply locale filtering in controllers/queries directly
    }
}