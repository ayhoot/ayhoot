<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Admin;
use App\Models\Institute;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('log', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->input('teacher_id'));
        }
        if ($request->filled('institute_id')) {
            $query->where('institute_id', $request->input('institute_id'));
        }
        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);
        $perPage = $request->input('per_page', 10);
        $activities = $query->paginate($perPage);
        foreach ($activities as $activity) {
            $activity->user = $this->getUserInfo($activity);
        }
        return response([
            'activities' => $activities,
            'pagination' => [
                'total' => $activities->total(),
                'per_page' => $activities->perPage(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage()
            ]
        ]);
    }

    
    private function getUserInfo($activity)
    {
        if (!is_null($activity->admin_id)) {
            $user = Admin::find($activity->admin_id);
            return $user ? ['id' => $user->id, 'name' => $user->name, 'role' => 'Admin'] : 'Unknown Admin';
        }
        
        if (!is_null($activity->teacher_id)) {
            $user = Teacher::find($activity->teacher_id);
            return $user ? ['id' => $user->id, 'name' => $user->first_name, 'role' => 'Teacher'] : 'Unknown Teacher';
        }
        
        if (!is_null($activity->institute_id)) {
            $user = Institute::find($activity->institute_id);
            return $user ? ['id' => $user->id, 'name' => $user->first_name, 'role' => 'Institute'] : 'Unknown Institute';
        }
        
        if (!is_null($activity->student_id)) {
            $user = Student::find($activity->student_id);
            return $user ? ['id' => $user->id, 'name' => $user->first_name, 'role' => 'Student'] : 'Unknown Student';
        }
        
        return 'System';
    }
}