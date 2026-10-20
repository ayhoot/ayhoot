<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LocaleService;
use Symfony\Component\HttpFoundation\Response;

class LocaleFilteringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply locale filtering if enabled
        if (LocaleService::isLocaleFilteringEnabled()) {
            // Set the current locale for the request
            $locale = LocaleService::getCurrentLocale();
            app()->setLocale($locale);
        }

        return $next($request);
    }
}