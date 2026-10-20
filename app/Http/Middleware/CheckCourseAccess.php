<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Student;

class CheckCourseAccess
{
    public function handle(Request $request, Closure $next)
    {
        $courseId = $request->route('course')?->id ?? $request->route('courseId');
        
        if (!$courseId) {
            return response(['message' => __('messages.course_not_found')], 404);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response(['message' => __('messages.course_not_found')], 404);
        }

        // If course is free, allow access
        if ($course->monetize == 'free') {
            return $next($request);
        }

        // Check user authentication and access
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        // Check if user has access
        if ($this->hasCourseAccess($user, $course)) {
            return $next($request);
        }

        return response(['message' => __('messages.course_access_denied')], 403);
    }

    private function getAuthenticatedUser()
    {
        if (auth('student')->check()) {
            return auth('student')->user();
        } elseif (auth('teacher')->check()) {
            return auth('teacher')->user();
        } elseif (auth('institute')->check()) {
            return auth('institute')->user();
        }
        return null;
    }

    private function hasCourseAccess($user, Course $course)
    {
        // Use the new access control method
        return $course->hasAccessFor($user);
    }
}