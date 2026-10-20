<?php

namespace App\Http\Middleware;

use App\Models\Institute;
use App\Models\Teacher;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = null;

        if (auth('teacher')->check()) {
            $user = Teacher::find(auth('teacher')->user()->id);
        } elseif (auth('institute')->check()) {
            $user = Institute::find(auth('institute')->user()->id);
        } else {
            return response(['message' => __('messages.unauthorized')], 401);
        }

        if (!method_exists($user, 'getFeatureValue')) {
            return response(['message' => __('messages.feature_checking_not_supported')], 403);
        }

        $value = $user->getFeatureValue($featureKey);

        if (!$value || $value == 0) {
            return response(['message' => __('messages.feature_not_enabled')], 403);
        }

        return $next($request);
    }
}
