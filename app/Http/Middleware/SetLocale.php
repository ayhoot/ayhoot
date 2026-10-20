<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\LocaleService;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Get locale using the LocaleService
        $locale = LocaleService::getCurrentLocale();
        
        // Set the application locale
        App::setLocale($locale);
        
        // Apply locale filtering if enabled
        if (LocaleService::isLocaleFilteringEnabled()) {
            $this->applyLocaleFiltering($locale);
        }

        return $next($request);
    }

    /**
     * Apply locale filtering to models
     */
    private function applyLocaleFiltering(string $locale): void
    {
        // Apply global scopes for locale filtering
        \App\Models\Course::addGlobalScope('locale_filter', function ($builder) use ($locale) {
            $builder->where('locale', $locale);
        });

        \App\Models\Exam::addGlobalScope('locale_filter', function ($builder) use ($locale) {
            $builder->where('locale', $locale);
        });

        \App\Models\Folder::addGlobalScope('locale_filter', function ($builder) use ($locale) {
            $builder->where('locale', $locale);
        });

        \App\Models\Group::addGlobalScope('locale_filter', function ($builder) use ($locale) {
            $builder->where('locale', $locale);
        });

        \App\Models\Question::addGlobalScope('locale_filter', function ($builder) use ($locale) {
            $builder->where('locale', $locale);
        });
    }
}