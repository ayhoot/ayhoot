<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        foreach ($roles as $role) {
            $user = auth($role)->user();
            if ($user && ($user->getTable() === $role . 's' || ($role === 'admin' && $user->getTable() === 'admins'))) {
                return $next($request);
            }
        }
        
        return response([
            'message' => 'You do not have permission to access this resource. Please log in with the correct account type.'
        ], 403);
    }
}
