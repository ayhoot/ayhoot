<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Label;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    /**
     * Discover content for authenticated users (excludes their own content)
     */
    public function discover(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        $categories = Category::withCount('exams')->orderBy('exams_count', 'desc')->get();
        
        // Get top ayhoots excluding user's own content
        $topAyhootsQuery = Exam::published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->whereNull('archive')
            ->orderBy('plays_count', 'desc')
            ->limit(6);
            
        $this->excludeOwnContent($topAyhootsQuery, $user, $userType);
        
        $top_ayhoots = $topAyhootsQuery->get()->map(function ($exam) {
            return array_merge($exam->toArray(), [
                'creator' => $exam->teacher ?? $exam->institute,
                'creator_type' => $exam->teacher ? 'teacher' : 'institute'
            ]);
        });

        $labels = Label::with([
            'exams' => fn($q) => $q->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions')->limit(10),
            'courses' => fn($q) => $q->with(['teacher', 'institute', 'category'])->withCount('students')->limit(10),
            'teachers' => fn($q) => $q->with(['plan', 'institutes'])->withCount('courses', 'students')->limit(10),
            'institutes' => fn($q) => $q->with(['plan'])->withCount('teachers', 'courses', 'students')->limit(10)
        ])->get();

        // Get recommended ayhoots excluding user's own content (limit to 4)
        $recommendedAyhootsQuery = Exam::whereHas('labels', function ($query) {
            $query->where('title', 'like', "%recommended%")->orWhere('description', 'like', "%recommended%");
        })
        ->with(['category', 'teacher', 'institute'])
        ->withCount('students')
        ->orderBy('id', 'desc')
        ->limit(4);
        
        $this->excludeOwnContent($recommendedAyhootsQuery, $user, $userType);
        
        $recommended_ayhoots = $recommendedAyhootsQuery->get()->map(function ($exam) {
            return array_merge($exam->toArray(), [
                'creator' => $exam->teacher ?? $exam->institute,
                'creator_type' => $exam->teacher ? 'teacher' : 'institute'
            ]);
        });

        // Get verified / top courses
        $verifiedCoursesQuery = Course::with(['category', 'teacher', 'institute'])
            ->withCount('students')
            ->whereNull('archive')
            ->orderBy('id', 'desc')
            ->limit(6);
        $this->excludeOwnContent($verifiedCoursesQuery, $user, $userType, 'course');
        $verified_courses = $verifiedCoursesQuery->get()->map(function ($course) {
            return array_merge($course->toArray(), [
                'creator' => $course->teacher ?? $course->institute,
                'creator_type' => $course->teacher ? 'teacher' : 'institute'
            ]);
        });

        return response([
            'categories' => $categories,
            'top_ayhoots' => $top_ayhoots,
            'verified_courses' => $verified_courses,
            'recommended_ayhoots' => $recommended_ayhoots,
            'labels' => $labels,
            'user_filtered' => true // Indicates content is filtered for this user
        ]);
    }

    /**
     * Discover content by category for authenticated users (excludes their own content)
     */
    public function category(Request $request, Category $category)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        // Get top ayhoots excluding user's own content
        $topAyhootsQuery = Exam::published()
            ->where('category_id', $category->id)
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->whereNull('archive')
            ->orderBy('plays_count', 'desc')
            ->limit(6);
            
        $this->excludeOwnContent($topAyhootsQuery, $user, $userType);
        
        $top_ayhoots = $topAyhootsQuery->get()->map(function ($exam) {
            return array_merge($exam->toArray(), [
                'creator' => $exam->teacher ?? $exam->institute,
                'creator_type' => $exam->teacher ? 'teacher' : 'institute'
            ]);
        });

        // Get verified courses excluding user's own content
        $verifiedCoursesQuery = Course::where('category_id', $category->id)
            ->whereNull('archive')
            ->with(['category', 'teacher', 'institute'])
            ->withCount('students')
            ->orderBy('id', 'desc')
            ->limit(6);
            
        $this->excludeOwnContent($verifiedCoursesQuery, $user, $userType, 'course');
        
        $verified_courses = $verifiedCoursesQuery->get()->map(function ($course) {
            return array_merge($course->toArray(), [
                'creator' => $course->teacher ?? $course->institute,
                'creator_type' => $course->teacher ? 'teacher' : 'institute'
            ]);
        });

        $labels = Label::with([
            'exams' => fn($q) => $q->where('category_id', $category->id)->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions')->limit(10),
            'courses' => fn($q) => $q->where('category_id', $category->id)->with(['teacher', 'institute', 'category'])->withCount('students')->limit(10),
            'teachers' => fn($q) => $q->with(['plan', 'institutes'])->withCount('courses', 'students')->limit(10),
            'institutes' => fn($q) => $q->with(['plan'])->withCount('teachers', 'courses', 'students')->limit(10)
        ])->get();

        return response([
            'top_ayhoots' => $top_ayhoots,
            'verified_courses' => $verified_courses,
            'labels' => $labels,
            'category' => $category,
            'user_filtered' => true // Indicates content is filtered for this user
        ]);
    }

    /**
     * Get discoverable exams for authenticated users (excludes their own content)
     */
    public function exams(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        $query = Exam::published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount('students', 'games as plays_count', 'questions')
            ->whereNull('archive')
            ->orderBy('id', 'desc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
            
        $this->excludeOwnContent($query, $user, $userType);
        
        $perPage = (int) $request->get('per_page', 10);
        $exams = $query->paginate($perPage)->through(function ($exam) {
            return array_merge($exam->toArray(), [
                'creator' => $exam->teacher ?? $exam->institute,
                'creator_type' => $exam->teacher ? 'teacher' : 'institute'
            ]);
        });

        return response([
            'exams' => $exams,
            'user_filtered' => true
        ]);
    }

    /**
     * Get discoverable courses for authenticated users
     */
    public function courses(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        $query = Course::with(['category', 'teacher', 'institute'])
            ->withCount('students')
            ->whereNull('archive')
            ->orderBy('id', 'desc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
            
        if ($request->boolean('exclude_own', true)) {
            $this->excludeOwnContent($query, $user, $userType, 'course');
        }
        
        $perPage = (int) $request->get('per_page', 10);
        $courses = $query->paginate($perPage)->through(function ($course) {
            return array_merge($course->toArray(), [
                'creator' => $course->teacher ?? $course->institute,
                'creator_type' => $course->teacher ? 'teacher' : 'institute'
            ]);
        });

        return response([
            'courses' => $courses,
            'user_filtered' => true
        ]);
    }

    /**
     * Exclude user's own content from query
     */
    private function excludeOwnContent($query, $user, $userType, $contentType = 'exam')
    {
        if (!$user) {
            return; // No filtering for unauthenticated users
        }

        if ($userType == 'teacher') {
            $query->where(function ($q) use ($user) {
                $q->whereNull('teacher_id')->orWhere('teacher_id', '!=', $user->id);
            });
        } elseif ($userType == 'institute') {
            $query->where(function ($q) use ($user) {
                $q->whereNull('institute_id')->orWhere('institute_id', '!=', $user->id);
            });
        }
        // Students don't own content, so no filtering needed
    }

    private function getAuthenticatedUser()
    {
        if (request()->user()) {
            return request()->user();
        } elseif (auth('teacher')->check()) {
            return auth('teacher')->user();
        } elseif (auth('institute')->check()) {
            return auth('institute')->user();
        } elseif (auth('student')->check()) {
            return auth('student')->user();
        } elseif (auth('sanctum')->check()) {
            return auth('sanctum')->user();
        } elseif (auth()->check()) {
            return auth()->user();
        }
        return null;
    }

    private function getUserType($user)
    {
        if ($user instanceof \App\Models\Student) return 'student';
        if ($user instanceof \App\Models\Teacher) return 'teacher';
        if ($user instanceof \App\Models\Institute) return 'institute';
        return null;
    }
}