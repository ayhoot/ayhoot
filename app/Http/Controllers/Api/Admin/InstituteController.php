<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstituteStoreRequest;
use App\Http\Requests\Admin\InstituteUpdateRequest;
use App\Http\Requests\Admin\InstituteUpdateStatusRequest;
use App\Models\Institute;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstituteController extends Controller
{
    public function index(Request $request)
    {
        $query = Institute::query()
            ->with('plan')
            ->withCount(['teachers', 'students', 'exams', 'courses', 'labels']);

        $query->when($request->search, function ($q, $search) {
            $q->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
            });
        });
        
        $sortBy = $request->input('sort', 'created_at');
        $sortDirection = $sortBy == 'created_at' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDirection);

        $institutes = $query->paginate(10)->appends($request->all());

        return response(['institutes' => $institutes]);
    }

    public function edit(Institute $institute)
    {
        $institute->loadCount(['teachers', 'students', 'exams', 'courses', 'labels'])->load('plan');
        return response(['institute' => $institute]);
    }

    public function update(InstituteUpdateRequest $request, Institute $institute)
    {
        $validated = $request->all();

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $institute->update($validated);
        return response(['message' => __('messages.institute_updated_successfully')]);
    }

    public function store(InstituteStoreRequest $request)
    {
        $validated = $request->validated();
        $institute = Institute::create($validated);

        return response([
            'institute' => $institute,
            'message' => 'Institute created successfully'
        ], 200);
    }

    public function delete(Institute $institute)
    {
        $institute->delete();
        return response(['message' => __('messages.institute_deleted_successfully')]);
    }
    
    public function updateStatus(InstituteUpdateStatusRequest $request, Institute $institute)
    {
        $validated = $request->validated();
        $institute->status = $validated['status'];
        $institute->save();

        return response([
            'message' => __('messages.institute_status_updated_successfully'),
            'institute' => $institute
        ]);
    }

    public function getTeachers(Institute $institute)
    {
        $teachers = $institute->teachers()->latest()->paginate(10);
        return response(['teachers' => $teachers]);
    }

    public function getStudents(Institute $institute)
    {
        $students = $institute->students()->latest()->paginate(10);
        return response(['students' => $students]);
    }

    public function getExams(Institute $institute)
    {
        $exams = $institute->exams()->with('teacher')->latest()->paginate(10);
        return response(['exams' => $exams]);
    }

    public function getCourses(Institute $institute)
    {
        $courses = $institute->courses()->with('teacher')->latest()->paginate(10);
        return response(['courses' => $courses]);
    }

    public function updatePlan(Request $request, Institute $institute)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $institute->plan_id = $validated['plan_id'];
        $institute->save();
        
        $institute->load('plan');

        return response([
            'message' => 'Institute plan updated successfully.',
            'institute' => $institute
        ]);
    }
}