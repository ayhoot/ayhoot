<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ResolveMultiGuardUser
{
    /**
     * Handle an incoming request to resolve which user model a Sanctum token belongs to.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the bearer token from the Authorization header
        $token = $request->bearerToken();
        
        \Log::info('ResolveMultiGuardUser middleware invoked', ['token_present' => $token ? true : false]);
        if ($token) {
            // Find the personal access token
            $personalAccessToken = PersonalAccessToken::findToken($token);
            \Log::info('ResolveMultiGuardUser token lookup', ['token' => substr($token,0,8) . '...', 'found' => $personalAccessToken ? true : false]);
            
            if ($personalAccessToken) {
                // Get the associated user (this will be the correct model: Student, Teacher, Institute, etc.)
                $user = $personalAccessToken->tokenable;
                \Log::info('ResolveMultiGuardUser tokenable', ['tokenable_class' => $user ? get_class($user) : null, 'tokenable_id' => $user ? ($user->id ?? null) : null]);
                
                if ($user) {
                    // Guard the user in all guards to make Auth::user() work
                    auth()->setUser($user);
                    \Log::info('ResolveMultiGuardUser auth set', ['user_id' => $user->id ?? null]);
                }
            }
        }
        
        return $next($request);
    }
}
