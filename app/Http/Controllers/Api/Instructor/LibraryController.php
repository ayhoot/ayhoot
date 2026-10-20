<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Course;
use App\Models\ExamPurchase;
use App\Models\CoursePurchase;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * Get user's library (purchased + owned content)
     */
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        $contentType = $request->get('type', 'all'); // 'all', 'exams', 'courses'
        
        $library = collect();
        
        // Get owned content
        if ($contentType == 'all' || $contentType == 'exams') {
            $ownedExams = $this->getOwnedExams($user, $userType);
            $library = $library->merge($ownedExams);
        }
        
        if ($contentType == 'all' || $contentType == 'courses') {
            $ownedCourses = $this->getOwnedCourses($user, $userType);
            $library = $library->merge($ownedCourses);
        }
        
        // Get purchased content
        if ($contentType == 'all' || $contentType == 'exams') {
            $purchasedExams = $this->getPurchasedExams($user, $userType);
            $library = $library->merge($purchasedExams);
        }
        
        if ($contentType == 'all' || $contentType == 'courses') {
            $purchasedCourses = $this->getPurchasedCourses($user, $userType);
            $library = $library->merge($purchasedCourses);
        }
        
        // Sort by most recent
        $library = $library->sortByDesc('added_at')->values();
        
        // Apply pagination
        $perPage = $request->get('per_page', 12);
        $currentPage = $request->get('page', 1);
        $total = $library->count();
        $items = $library->forPage($currentPage, $perPage);

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        return response([
            'library' => $paginated,
            'summary' => [
                'total_items' => $total,
                'owned_count' => $library->where('ownership_type', 'owned')->count(),
                'purchased_count' => $library->where('ownership_type', 'purchased')->count(),
                'current_filter' => $contentType
            ]
        ]);
    }

    /**
     * Get assignable content for creating games
     */
    public function getAssignableContent(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        // Only get exams (courses can't be assigned as games)
        $assignableExams = collect();
        
        // Get owned exams
        $ownedExams = $this->getOwnedExams($user, $userType);
        $assignableExams = $assignableExams->merge($ownedExams);
        
        // Get purchased exams
        $purchasedExams = $this->getPurchasedExams($user, $userType);
        $assignableExams = $assignableExams->merge($purchasedExams);
        
        // Filter only published exams with questions
        $assignableExams = $assignableExams->filter(function ($exam) {
            return $exam['status'] == 'published' && $exam['questions_count'] > 0;
        });
        
        return response([
            'assignable_exams' => $assignableExams->values(),
            'total_count' => $assignableExams->count()
        ]);
    }

    /**
     * Get content details for assignment
     */
    public function getContentForAssignment(Request $request, $contentType, $contentId)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        if ($contentType == 'exam') {
            $exam = Exam::with(['questions', 'teacher', 'institute', 'category'])
                ->find($contentId);
                
            if (!$exam) {
                return response(['message' => 'Exam not found'], 404);
            }
            
            // Check if user has access to this exam
            if (!$this->hasAccessToContent($user, $userType, $exam, 'exam')) {
                return response(['message' => 'You do not have access to this exam'], 403);
            }
            
            return response([
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'cover' => $exam->cover,
                    'questions_count' => $exam->questions->count(),
                    'duration' => $exam->questions->sum('duration'),
                    'category' => $exam->category,
                    'creator' => $exam->teacher ?? $exam->institute,
                    'can_assign' => true
                ]
            ]);
        }
        
        return response(['message' => 'Invalid content type'], 400);
    }

    private function getOwnedExams($user, $userType)
    {
        $query = Exam::with(['teacher', 'institute', 'category'])
            ->withCount('questions');
            
        if ($userType == 'teacher') {
            $query->where('teacher_id', $user->id);
        } elseif ($userType == 'institute') {
            $query->where('institute_id', $user->id);
        } else {
            return collect(); // Students don't own exams
        }
        
        return $query->get()->map(function ($exam) {
            return [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'cover' => $exam->cover,
                'content_type' => 'exam',
                'ownership_type' => 'owned',
                'status' => $exam->status,
                'monetize' => $exam->monetize,
                'price' => $exam->price,
                'questions_count' => $exam->questions_count,
                'creator' => $exam->teacher ?? $exam->institute,
                'category' => $exam->category,
                'added_at' => $exam->created_at,
                'can_assign' => true,
                'can_edit' => true
            ];
        });
    }

    private function getOwnedCourses($user, $userType)
    {
        $query = Course::with(['teacher', 'institute', 'category'])
            ->withCount('exams');
            
        if ($userType == 'teacher') {
            $query->where('teacher_id', $user->id);
        } elseif ($userType == 'institute') {
            $query->where('institute_id', $user->id);
        } else {
            return collect(); // Students don't own courses
        }
        
        return $query->get()->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'cover' => $course->cover,
                'content_type' => 'course',
                'ownership_type' => 'owned',
                'status' => $course->status,
                'monetize' => $course->monetize,
                'price' => $course->price,
                'exams_count' => $course->exams_count,
                'creator' => $course->teacher ?? $course->institute,
                'category' => $course->category,
                'added_at' => $course->created_at,
                'can_assign' => false, // Courses can't be assigned as games
                'can_edit' => true
            ];
        });
    }

    private function getPurchasedExams($user, $userType)
    {
        $purchases = ExamPurchase::with(['exam.teacher', 'exam.institute', 'exam.category'])
            ->where('purchaser_id', $user->id)
            ->where('purchaser_type', $userType)
            ->where('payment_status', 'completed')
            ->get();
            
        return $purchases->map(function ($purchase) {
            $exam = $purchase->exam;
            return [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'cover' => $exam->cover,
                'content_type' => 'exam',
                'ownership_type' => 'purchased',
                'status' => $exam->status,
                'monetize' => $exam->monetize,
                'price' => $exam->price,
                'amount_paid' => $purchase->amount_paid,
                'questions_count' => $exam->questions()->count(),
                'creator' => $exam->teacher ?? $exam->institute,
                'category' => $exam->category,
                'added_at' => $purchase->purchased_at,
                'purchase_date' => $purchase->purchased_at,
                'can_assign' => true,
                'can_edit' => false
            ];
        });
    }

    private function getPurchasedCourses($user, $userType)
    {
        $purchases = CoursePurchase::with(['course.teacher', 'course.institute', 'course.category'])
            ->where('purchaser_id', $user->id)
            ->where('purchaser_type', $userType)
            ->where('payment_status', 'completed')
            ->get();
            
        return $purchases->map(function ($purchase) {
            $course = $purchase->course;
            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'cover' => $course->cover,
                'content_type' => 'course',
                'ownership_type' => 'purchased',
                'status' => $course->status,
                'monetize' => $course->monetize,
                'price' => $course->price,
                'amount_paid' => $purchase->amount_paid,
                'exams_count' => $course->exams()->count(),
                'creator' => $course->teacher ?? $course->institute,
                'category' => $course->category,
                'added_at' => $purchase->purchased_at,
                'purchase_date' => $purchase->purchased_at,
                'can_assign' => false, // Courses can't be assigned as games
                'can_edit' => false
            ];
        });
    }

    private function hasAccessToContent($user, $userType, $content, $contentType)
    {
        // Check if user owns the content
        if ($contentType == 'exam') {
            if (($userType == 'teacher' && $content->teacher_id == $user->id) ||
                ($userType == 'institute' && $content->institute_id == $user->id)) {
                return true;
            }
        }
        
        // Check if user purchased the content
        if ($contentType == 'exam') {
            return ExamPurchase::where('exam_id', $content->id)
                ->where('purchaser_id', $user->id)
                ->where('purchaser_type', $userType)
                ->where('payment_status', 'completed')
                ->exists();
        }
        
        return false;
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

    private function getUserType($user)
    {
        if ($user instanceof \App\Models\Student) return 'student';
        if ($user instanceof \App\Models\Teacher) return 'teacher';
        if ($user instanceof \App\Models\Institute) return 'institute';
        return null;
    }
}