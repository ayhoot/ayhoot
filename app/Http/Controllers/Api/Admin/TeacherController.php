<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherStoreRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query()
            ->with(['plan', 'labels']) 
            ->withCount(['courses', 'students', 'exams']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('username', 'like', "%" . $request->search . "%")
                  ->orWhere('email', 'like', "%" . $request->search . "%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%" . $request->search . "%"]);
            });
        }
        
        if ($request->sort == 'name') {
            $query->orderBy('first_name')->orderBy('last_name');
        } else {
            $query->latest('created_at');
        }

        return response(['teachers' => $query->paginate(10)]);
    }

    // --- THIS IS THE FIX ---
    // This method was missing. It's required for the profile page to load.
    public function show(Teacher $teacher)
    {
        $teacher->load(['plan', 'institutes'])
                ->loadCount(['courses', 'exams', 'students']);

        return response(['teacher' => $teacher]);
    }

    public function store(TeacherStoreRequest $request)
    {
        $teacher = Teacher::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        
        return response(['message' => __('messages.teacher_created_successfully'), 'data' => $teacher], 200);
    }

    public function delete(Teacher $teacher)
    {
        $teacher->delete();
        return response(['message' => __('messages.teacher_deleted_successfully')]);
    }

    public function verify(Teacher $teacher)
    {
        $teacher->verified_at = now();
        $teacher->save();
        return response(['message' => __('messages.teacher_verified_successfully')]);
    }

    public function unverify(Teacher $teacher)
    {
        $teacher->verified_at = null;
        $teacher->save();
        return response(['message' => __('messages.teacher_unverified_successfully')]);
    }

    public function ban(Teacher $teacher)
    {
        $teacher->status = 'block';
        $teacher->save();
        return response(['message' => __('messages.teacher_banned_successfully')]);
    }

    public function unban(Teacher $teacher)
    {
        $teacher->status = 'active';
        $teacher->save();
        return response(['message' => __('messages.teacher_unbanned_successfully')]);
    }

    public function getCourses(Teacher $teacher)
    {
        $courses = $teacher->courses()->latest()->paginate(10);
        return response(['courses' => $courses]);
    }

    public function getExams(Teacher $teacher)
    {
        $exams = $teacher->exams()->latest()->paginate(10);
        return response(['exams' => $exams]);
    }

    public function updatePlan(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $teacher->plan_id = $validated['plan_id'];
        $teacher->save();
        
        $teacher->load('plan');

        return response([
            'message' => 'Teacher plan updated successfully.',
            'teacher' => $teacher
        ]);
    }
}