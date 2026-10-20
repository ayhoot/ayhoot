<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::query();
        if ($request->search) {
            $students = $students->where('first_name', 'like', "%" . $request->search . "%")
                ->orWhere('email', 'like', "%" . $request->search . "%")
                ->orWhere('phone', 'like', "%" . $request->search . "%");
        }
        return response([
            'students' => $students->paginate(10)
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $student = Student::create($data);
        return response([
            'message' => __('messages.student_created_successfully'),
            'data' => $student
        ]);
    }

    public function edit(Student $student)
    {
        $student->load('institutes');

        $studentAnswers = Answer::where('student_id', $student->id)
            ->with(['exam.questions', 'exam.teacher', 'exam.institute', 'game'])
            ->get();

        $processedAnswers = $studentAnswers->map(function ($answer) {
            if (!$answer->exam) {
                $answer->rank = null;
                $answer->score_details = ['correct' => 0, 'total' => 0];
                return $answer;
            }

            $allAttempts = Answer::where('exam_id', $answer->exam_id)
                ->where('game_id', $answer->game_id)
                ->with('exam.questions')
                ->get();

            $scores = $allAttempts->map(function ($attempt) {
                return [
                    'student_id' => $attempt->student_id,
                    'score' => $this->calculateTotalScore($attempt),
                ];
            });

            $rankedScores = $scores->sortByDesc('score')->values();

            $rankIndex = $rankedScores->search(function ($item) use ($answer) {
                return $item['student_id'] == $answer->student_id;
            });

            $answer->rank = $rankIndex !== false ? $rankIndex + 1 : null;
            
            $correctCount = 0;
            $totalScorableQuestions = 0;
            $answeredEntries = $answer->answers ?? [];
            $questionsKeyed = $answer->exam->questions->keyBy('id');

            foreach($answeredEntries as $entry) {
                $question = $questionsKeyed->get($entry['question_id']);
                if ($question && $question->type !== 'slide') {
                    $totalScorableQuestions++;
                    if ($entry['is_correct'] ?? false) {
                        $correctCount++;
                    }
                }
            }
            
            $answer->score_details = [
                'correct' => $correctCount,
                'total' => $totalScorableQuestions > 0 ? $totalScorableQuestions : $answer->exam->questions->where('type', '!=', 'slide')->count()
            ];

            return $answer;
        });

        return response([
            'student' => $student,
            'answers' => $processedAnswers,
        ]);
    }

    public function getCourse(Student $student)
    {
        $courses = $student->courses()->with(['institute', 'teacher'])->get();

        return response([
            'courses' => $courses
        ]);
    }

    protected function calculateTotalScore(Answer $answer): int
    {
        $totalPointsEarned = 0;
        $studentAnswerEntries = $answer->answers ?? [];
        $examQuestions = $answer->exam?->questions;

        if (empty($studentAnswerEntries) || !$examQuestions) {
            return 0;
        }

        $questionsKeyedById = $examQuestions->keyBy('id');

        foreach ($studentAnswerEntries as $entry) {
            $questionId = $entry['question_id'] ?? null;
            if (!$questionId) continue;

            $question = $questionsKeyedById->get($questionId);

            if ($question && $question->type !== 'slide') {
                $isCorrect = $entry['is_correct'] ?? false;
                if ($isCorrect) {
                    $totalPointsEarned += ($question->points > 0 ? $question->points : 1000);
                }
            }
        }

        return $totalPointsEarned;
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->all();
        $student->update($data);
        return response([
            'message' => __('messages.student_updated_successfully'),
            'data' => $student
        ]);
    }

    public function delete(Student $student)
    {
        $student->delete();
        return response([
            'message' => __('messages.student_deleted_successfully'),
            'student' => $student
        ]);
    }
}