<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SaveAnswerRequest;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Game;
use App\Services\AnswerService;
use Illuminate\Http\Request;

class ExamAnswerController extends Controller
{
    protected $answerService;

    public function __construct()
    {
        $this->answerService = new AnswerService();
    }

    /**
     * Save student answers
     */
    public function saveAnswer(SaveAnswerRequest $request)
    {
        try {
            $student = auth('student')->user();
            
            if (!$student) {
                return response(['message' => __('messages.unauthenticated')], 401);
            }

            $result = $this->answerService->saveAnswers(
                $request->input('attempt_id'),
                $request->input('answers'),
                $student,
                $request->input('is_live_session', false),
                $request->input('live_session_id')
            );

            return response([
                'message' => __('messages.answers_processed_successfully'),
                'results' => $result['results'],
                'attempt_status' => $result['attempt_status'],
                'remaining_time' => $result['remaining_time']
            ]);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'exam_timed_out' => str_contains($e->getMessage(), 'expired')
            ], str_contains($e->getMessage(), 'expired') ? 400 : 500);
        }
    }

    /**
     * Get student's answer for a specific question
     */
    public function getStudentAnswerToQuestion(
        Request $request, 
        Exam $exam, 
        Question $question, 
        Game $game = null
    ) {
        try {
            $student = auth('student')->user();
            
            if (!$student) {
                return response(['message' => __('messages.unauthenticated')], 401);
            }

            $answer = $this->answerService->getStudentAnswer($student, $exam, $question, $game);

            if (!$answer) {
                return response(['message' => __('messages.no_answer_found')]);
            }

            return response($answer);

        } catch (\Exception $e) {
            return response([
                'message' => __('messages.error_retrieving_answer'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}