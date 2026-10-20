<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Services\AnswerService;
use Illuminate\Http\Request;

class ExamReportController extends Controller
{
    protected $answerService;

    public function __construct()
    {
        $this->answerService = new AnswerService();
    }

    /**
     * Get comprehensive exam report
     */
    public function getExamAttemptReport(Request $request, $answerId)
    {
        

        $student = auth('student')->user();
        
        // Check if student is authenticated
        if (!$student) {
           
            
            return response([
                'message' => 'Authentication required. Please log in to view your exam report.'
            ], 401);
        }

        

        // Find the answer record
        $answer = Answer::find($answerId);
        if (!$answer) {
            
            
            return response([
                'message' => 'Exam report not found. The exam attempt may not exist or may have been deleted.'
            ], 404);
        }

      

        // Check if the answer belongs to the authenticated student
        if ((int)$answer->student_id !== (int)$student->id) {
            
            
            return response([
                'message' => 'You do not have permission to view this exam report. This report belongs to another student.'
            ], 403);
        }

        // Check if the answer/attempt exists and has some data
        // Allow both 'started' and 'completed' status
        if (!$answer->started_at) {
            return response([
                'message' => 'This exam attempt has no data yet. Please start the exam first.'
            ], 400);
        }

        // If exam is still in progress, allow access but show appropriate message
        if ($answer->status == 'started') {
           
        }

        try {
            $report = $this->answerService->generateExamReport($answer->id, $student);
            
           
            
            return response(['exam_report' => $report]);

        } catch (\Exception $e) {


            return response([
                'message' => 'Unable to generate exam report at this time. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get student's Answer records
     */
    public function getMyAnswers(Request $request)
    {
        $student = auth('student')->user();
        
        if (!$student) {
            return response(['message' => 'Not authenticated'], 401);
        }
        
        $answers = Answer::where('student_id', $student->id)
            ->with('exam:id,title')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($answer) {
                return [
                    'id' => $answer->id,
                    'exam_id' => $answer->exam_id,
                    'exam_title' => $answer->exam->title ?? 'Unknown',
                    'status' => $answer->status,
                    'created_at' => $answer->created_at,
                    'completed_at' => $answer->completed_at,
                ];
            });
        
        return response([
            'student_id' => $student->id,
            'student_email' => $student->email,
            'answers' => $answers,
            'total_answers' => $answers->count()
        ]);
    }

    /**
     * Simple authentication test
     */
    public function testAuth(Request $request)
    {
        $student = auth('student')->user();
        
        return response([
            'message' => 'Authentication test',
            'authenticated' => $student ? true : false,
            'student_id' => $student ? $student->id : null,
            'student_email' => $student ? $student->email : null,
            'guard' => 'student',
            'default_guard' => auth()->getDefaultDriver(),
            'has_bearer_token' => $request->bearerToken() ? true : false,
            'user_agent' => $request->header('User-Agent'),
            'middleware_passed' => true
        ]);
    }

    /**
     * Debug endpoint to check answer details
     */
    public function debugAnswer(Request $request, $answerId)
    {
        $student = auth('student')->user();
        
        $debugInfo = [
            'answer_id' => $answerId,
            'student_authenticated' => $student ? true : false,
            'student_id' => $student ? $student->id : null,
            'student_email' => $student ? $student->email : null,
            'auth_guard' => auth()->getDefaultDriver(),
            'has_bearer_token' => $request->bearerToken() ? true : false,
        ];
        
        if ($student) {
            $answer = Answer::find($answerId);
            $debugInfo['answer_exists'] = $answer ? true : false;
            
            if ($answer) {
                $debugInfo['answer_student_id'] = $answer->student_id;
                $debugInfo['answer_status'] = $answer->status;
                $debugInfo['answer_exam_id'] = $answer->exam_id;
                $debugInfo['answer_created_at'] = $answer->created_at;
                $debugInfo['owns_answer'] = $answer->student_id == $student->id;
            }
        }
        
        return response(['debug_info' => $debugInfo]);
    }

    /**
     * Get student's attended exams with performance data
     */
    public function getStudentExamAttended(Request $request)
    {
        $student = auth('student')->user();
        
        if (!$student) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        try {
            $filters = [
                'search' => $request->input('search'),
                'per_page' => $request->input('per_page', 10)
            ];

            $exams = $this->answerService->getAttendedExams($student, $filters);
            
            return response(['exams' => $exams]);

        } catch (\Exception $e) {
            return response([
                'message' => __('messages.error_retrieving_exams'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}