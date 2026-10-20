<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use App\Models\Teacher;
use App\Models\Institute;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Get all permissions for the authenticated user
     */
    public function index()
    {
        $user = null;

        if (auth('teacher')->check()) {
            $user = Teacher::find(auth('teacher')->user()->id);
        } elseif (auth('institute')->check()) {
            $user = Institute::find(auth('institute')->user()->id);
        } else {
            return response(['message' => __('messages.unauthorized')], 401);
        }

        $permissions = $this->permissionService->getAllPermissions($user);

        return response([
            'permissions' => $permissions,
            'user_type' => $user instanceof Teacher ? 'teacher' : 'institute',
            'success' => true
        ]);
    }

    /**
     * Check specific permission
     */
    public function check(Request $request)
    {
        $request->validate([
            'permission' => 'required|string|in:create_exam,create_course,create_group,add_tutee,monetize'
        ]);

        $user = null;

        if (auth('teacher')->check()) {
            $user = Teacher::find(auth('teacher')->user()->id);
        } elseif (auth('institute')->check()) {
            $user = Institute::find(auth('institute')->user()->id);
        } else {
            return response(['message' => __('messages.unauthorized')], 401);
        }

        $result = match($request->permission) {
            'create_exam' => $this->permissionService->canCreateExam($user),
            'create_course' => $this->permissionService->canCreateCourse($user),
            'create_group' => $this->permissionService->canCreateGroup($user),
            'add_tutee' => $this->permissionService->canAddTutee($user),
            'monetize' => $this->permissionService->canMonetize($user),
            default => ['allowed' => false, 'reason' => 'Unknown permission type']
        };

        return response([
            'permission' => $request->permission,
            'result' => $result,
            'success' => true
        ]);
    }

    /**
     * Check participant limit for specific content
     */
    public function checkParticipantLimit(Request $request)
    {
        $request->validate([
            'current_participants' => 'required|integer|min:0'
        ]);

        $user = null;

        if (auth('teacher')->check()) {
            $user = Teacher::find(auth('teacher')->user()->id);
        } elseif (auth('institute')->check()) {
            $user = Institute::find(auth('institute')->user()->id);
        } else {
            return response(['message' => __('messages.unauthorized')], 401);
        }

        $result = $this->permissionService->canAddParticipant($user, $request->current_participants);

        return response([
            'result' => $result,
            'success' => true
        ]);
    }

    /**
     * Get usage statistics
     */
    public function usage()
    {
        $user = null;

        if (auth('teacher')->check()) {
            $user = Teacher::find(auth('teacher')->user()->id);
        } elseif (auth('institute')->check()) {
            $user = Institute::find(auth('institute')->user()->id);
        } else {
            return response(['message' => __('messages.unauthorized')], 401);
        }

        $permissions = $this->permissionService->getAllPermissions($user);
        
        // Calculate usage percentages
        $usage = [];
        foreach (['exams', 'courses', 'groups', 'tutees'] as $type) {
            if (isset($permissions[$type]['limit']) && $permissions[$type]['limit'] > 0) {
                $current = $permissions[$type]['current'] ?? 0;
                $limit = $permissions[$type]['limit'];
                $usage[$type] = [
                    'current' => $current,
                    'limit' => $limit,
                    'percentage' => round(($current / $limit) * 100, 1),
                    'remaining' => $limit - $current
                ];
            }
        }

        return response([
            'usage' => $usage,
            'plan' => $permissions['plan'],
            'success' => true
        ]);
    }
}
