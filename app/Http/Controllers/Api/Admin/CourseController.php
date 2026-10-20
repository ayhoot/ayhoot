<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStatusCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['institute', 'teacher', 'exams','labels']);
        $query->when($request->search, function ($q, $search) {
            return $q->where('title', 'like', "%{$search}%");
        });

        $query->when($request->status, function ($q, $status) {
            return $q->where('status', $status);
        });

        $query->when($request->accessibility, function ($q, $accessibility) {
            return $q->where('monetize', $accessibility);
        });

        $sort_by = $request->input('sort', 'created_at');
        $sort_direction = $sort_by == 'created_at' ? 'desc' : 'asc';
        $query->orderBy($sort_by, $sort_direction);

        $paginator = $query->paginate(10);

        $courses = $paginator->appends($request->only(['search', 'status', 'accessibility', 'sort']));

        return response(['courses' => $courses]);
    }
    public function edit(Course $course)
    {
        return response([
            'course' => $course->load(['institute', 'teacher', 'exams','labels'])
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $course->update($request->all());
        return response([
            'message' => __('messages.course_updated_successfully')
        ]);
    }

    public function store(Request $request)
    {
        $course = new Course();
        $course->fill($request->all());
        $course->save();
        return response([
            'message' => __('messages.course_created_successfully')
        ]);
    }

    public function delete(Course $course)
    {
        $course->delete();
        return response([
            'message' => __('messages.course_deleted_successfully')
        ]);
    }

    public function updateStatus(UpdateStatusCourseRequest $request,Course $course){
        $course->status=$request->status;
        $course->save();
        return response([
            'course'=>$course,
            'message'=> __('messages.course_status_updated_successfully')
        ]);
    }
}
