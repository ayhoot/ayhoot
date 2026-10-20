<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use App\Models\Teacher;
use App\Models\Institute;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = null;

        // Get the authenticated user
        if (auth('teacher')->check()) {
            $user = Teacher::find(auth('teacher')->user()->id);
        } elseif (auth('institute')->check()) {
            $user = Institute::find(auth('institute')->user()->id);
        } else {
            return response(['message' => __('messages.unauthorized')], 401);
        }

        // Check the specific permission
        $result = match($permission) {
            'create_exam' => $this->permissionService->canCreateExam($user),
            'create_course' => $this->permissionService->canCreateCourse($user),
            'create_group' => $this->permissionService->canCreateGroup($user),
            'add_tutee' => $this->permissionService->canAddTutee($user),
            'monetize' => $this->permissionService->canMonetize($user),
            default => ['allowed' => false, 'reason' => 'Unknown permission type']
        };

        if (!$result['allowed']) {
            return response([
                'message' => $result['reason'],
                'permission_denied' => true,
                'permission_type' => $permission,
                'details' => $result
            ], 403);
        }

        // Add permission info to request for use in controller
        $request->merge(['permission_info' => $result]);

        return $next($request);
    }
}
