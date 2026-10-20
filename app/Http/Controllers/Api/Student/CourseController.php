<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function show(Course $course)
    {
        $course->load(['exams', 'labels', 'teacher', 'institute', 'category']);
        
        $student = auth('student')->user();
        $hasAccess = false;
        $needsPurchase = false;
        $purchased = 'no';

        if ($course->monetize == 'free') {
            $hasAccess = true;
            $purchased = 'yes';
        } elseif ($student) {
            // Use the unified access control method
            $hasAccess = $course->hasAccessFor($student);
            
            if ($hasAccess) {
                $purchased = 'yes';
            } else {
                $needsPurchase = true;
            }
        } else {
            $needsPurchase = true;
        }

        // Add purchase status to course object
        $course->purchased = $purchased;
        
        return response([
            'course' => $course,
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_free' => $course->monetize == 'free'
        ]);
    }

    public function index(Request $request)
    {
        $student = Student::find(auth('student')->user()->id);
        $courses = $student->courses()->with(['teacher', 'institute', 'category'])->withCount('students')->orderBy('id', 'desc');
        
        if ($request->search) {
            $courses = $courses->where('title', 'like', "%" . $request->search . "%");
        }
        
        if ($request->plays) {
            $courses = $courses->where('students_count', '>=', $request->plays);
        }
        
        return response(['courses' => $courses->paginate(10)]);
    }

    public function purchased(Request $request)
    {
        $student = auth('student')->user();
        
        // Get courses from both old pivot table and new purchase system
        $pivotCourseIds = $student->courses()->pluck('course_id');
        
        $purchasedCourseIds = \App\Models\CoursePurchase::where('purchaser_id', $student->id)
            ->where('purchaser_type', 'App\Models\Student')
            ->where('payment_status', 'completed')
            ->pluck('course_id');
        
        $allCourseIds = $pivotCourseIds->merge($purchasedCourseIds)->unique();
        
        $courses = Course::whereIn('id', $allCourseIds)
            ->with(['teacher', 'institute', 'category'])
            ->withCount('students')
            ->orderBy('id', 'desc');
        
        if ($request->search) {
            $courses = $courses->where('title', 'like', "%" . $request->search . "%");
        }
        
        return response(['courses' => $courses->paginate(10)]);
    }
}