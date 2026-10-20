<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamPurchase;
use App\Models\Game;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * Get list of exams for student library
     */
    public function index(Request $request)
    {
        $student = auth('student')->user();
        if (!$student) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        // Gather accessible exams for student:
        // 1. Assigned via student's groups (games)
        $assignedExamIds = Game::whereHas('groups.students', function ($q) use ($student) {
            $q->where('students.id', $student->id);
        })->pluck('exam_id');

        // 2. Purchased directly
        $purchasedExamIds = ExamPurchase::where('purchaser_id', $student->id)
            ->where(function ($q) {
                $q->where('purchaser_type', 'student')
                  ->orWhere('purchaser_type', 'App\\Models\\Student');
            })
            ->where('payment_status', 'completed')
            ->pluck('exam_id');

        // 3. Legacy exam_student pivot
        $legacyExamIds = $student->exams()->pluck('exams.id');

        // 4. Exams part of enrolled/purchased courses
        $courseExamIds = Exam::whereHas('courses.students', function ($q) use ($student) {
            $q->where('students.id', $student->id);
        })->pluck('id');

        // 5. Attempted exams
        $attemptedExamIds = Answer::where('student_id', $student->id)->pluck('exam_id');

        $accessibleExamIds = $assignedExamIds
            ->concat($purchasedExamIds)
            ->concat($legacyExamIds)
            ->concat($courseExamIds)
            ->concat($attemptedExamIds)
            ->unique()
            ->filter();

        $query = Exam::query()->whereIn('id', $accessibleExamIds);
        
        if ($request->search) {
            $query->where('title', 'like', "%" . $request->search . "%");
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $exams = $query->with(['teacher', 'institute', 'category'])
            ->withCount('games as plays_count', 'questions')
            ->latest('id')
            ->paginate(10);
            
        $assignedGamesByExam = Game::whereHas('groups.students', function ($q) use ($student) {
            $q->where('students.id', $student->id);
        })
        ->latest('id')
        ->get()
        ->groupBy('exam_id');

        $completedGameIds = Answer::where('student_id', $student->id)
            ->whereNotNull('game_id')
            ->where('status', 'completed')
            ->pluck('game_id')
            ->toArray();

        // Add students_count using the accessor for each exam and assignment info
        $exams->getCollection()->transform(function ($exam) use ($assignedGamesByExam, $completedGameIds) {
            $exam->students_count = $exam->students_count;

            $assignedGame = $assignedGamesByExam->get($exam->id)?->first();
            if ($assignedGame) {
                $exam->is_assigned = true;
                $exam->assigned_game_id = $assignedGame->id;
                $exam->assignment_completed = in_array($assignedGame->id, $completedGameIds);
            } else {
                $exam->is_assigned = false;
                $exam->assigned_game_id = null;
                $exam->assignment_completed = false;
            }

            return $exam;
        });
        
        return response(['exams' => $exams]);
    }
}
