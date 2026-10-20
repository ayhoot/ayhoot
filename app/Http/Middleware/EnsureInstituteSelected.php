<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstituteSelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('teacher')->user();

        // Ensure user is authenticated
        if (!$user) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        // Check if institute_id exists in the current access token
        $token = $user->currentAccessToken();
        if (!$token || empty($token->institute_id)) {
            return response(['message' => __('messages.institute_not_selected')], 403);
        }

        return $next($request);
    }
}
