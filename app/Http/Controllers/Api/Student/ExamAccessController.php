<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Game;
use App\Services\AnswerService;
use Illuminate\Http\Request;

class ExamAccessController extends Controller
{
    protected $answerService;

    public function __construct()
    {
        $this->answerService = new AnswerService();
    }

    /**
     * Get exam details with access information
     */
    public function show(Request $request, Exam $exam)
    {
        $student = auth('student')->user();
        $gameId = $request->query('game') ?? $request->query('game_id');
        $hasAccess = false;
        $needsPurchase = false;
        $purchased = 'no';

        if ($exam->monetize == 'free') {
            $hasAccess = true;
        } elseif ($student) {
            $hasAccess = $exam->hasAccessFor($student, $gameId);

            if ($hasAccess) {
                $purchased = $exam->isPurchasedBy($student->id, 'student') ? 'yes' : 'no';
            } else {
                $needsPurchase = true;
            }
        } else {
            $needsPurchase = true;
        }

        return response([
            'exam' => $exam,
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_free' => $exam->monetize == 'free',
            'purchased' => $purchased,
            'price' => $exam->price
        ]);
    }

    /**
     * Get exam for gameplay
     */
    public function getGame(Request $request, Exam $exam, Game $game = null)
    {
        $student = auth('student')->user();

        if (!$student) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        // Resolve game if not passed in route parameter
        if (!$game) {
            if ($request->filled('game_id')) {
                $game = Game::find($request->input('game_id'));
            } elseif ($request->filled('game')) {
                $game = Game::find($request->input('game'));
            }
        }

        if ($game && $game->exam_id != $exam->id) {
            $game = null;
        }

        // Check access using unified method with game assignment support
        if (!$exam->hasAccessFor($student, $game?->id)) {
            return response([
                'message' => __('messages.exam_purchase_required'),
                'exam' => $exam,
                'needs_purchase' => true,
                'price' => $exam->price,
                'monetize' => $exam->monetize
            ], 402);
        }

        try {
            // Check for existing attempt strictly for this session mode:
            // Assignment mode: look up attempt for this game_id.
            // Standalone mode: look up attempt where game_id is null.
            $existingAttempt = \App\Models\Answer::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->where(function ($q) use ($game) {
                    if ($game) {
                        $q->where('game_id', $game->id);
                    } else {
                        $q->whereNull('game_id');
                    }
                })
                ->latest('id')
                ->first();

            // Load questions with proper relations
            $exam->load(['questions' => function ($query) {
                $query->select([
                    'id',
                    'exam_id',
                    'title',
                    'type',
                    'options',
                    'q_options',
                    'files',
                    'duration',
                    'points',
                    'slide',
                    'sequence_options'
                ]);
            }]);

            // Ensure options are properly decoded for all questions
            $exam->questions->each(function ($question) {
                // If options is still a JSON string, decode it
                if (is_string($question->options)) {
                    $question->setAttribute('options', json_decode($question->options, true) ?? []);
                }
                
                if (is_string($question->q_options)) {
                    $question->setAttribute('q_options', json_decode($question->q_options, true) ?? []);
                }
                if (is_string($question->sequence_options)) {
                    $question->setAttribute('sequence_options', json_decode($question->sequence_options, true) ?? []);
                }
            });

            // If there's an existing attempt and the caller did not explicitly request to reset or use it,
            // return minimal info so frontend can ask the student whether to reset previous data.
            if ($existingAttempt && !$request->boolean('reset_attempt') && !$request->boolean('use_existing_attempt')) {
                return response([
                    'exam' => $exam,
                    'previous_attempt_exists' => true,
                    'previous_attempt' => [
                        'id' => $existingAttempt->id,
                        'status' => $existingAttempt->status,
                        'answers' => $existingAttempt->answers ?? [],
                        'remaining_time_seconds' => $existingAttempt->remaining_time,
                        'started_at' => $existingAttempt->started_at?->toIso8601String(),
                        'completed_at' => $existingAttempt->completed_at?->toIso8601String(),
                    ],
                    'server_time_at_load' => now()->timestamp,
                ]);
            }

            // If requested, delete the existing attempt to reset data
            if ($existingAttempt && $request->boolean('reset_attempt')) {
                try {
                    $existingAttempt->delete();
                } catch (\Exception $e) {
                    // Log and continue - deletion failure shouldn't block creating a fresh attempt
                    \Illuminate\Support\Facades\Log::error('Failed to delete existing attempt: ' . $e->getMessage());
                }
            }

            // If caller asked to use existing attempt, use it; otherwise create a new one
            if ($existingAttempt && $request->boolean('use_existing_attempt')) {
                $attempt = $existingAttempt;
            } else {
                $attempt = $this->answerService->startExamAttempt($student, $exam, $game);
            }

            return response([
                'exam' => $exam,
                'attempt_id' => $attempt->id,
                'game_id' => $game?->id ?? $attempt->game_id,
                'is_assignment' => !empty($game?->id ?? $attempt->game_id),
                'remaining_time_seconds' => $attempt->remaining_time,
                'server_time_at_load' => now()->timestamp,
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'attempt_completed' => str_contains($e->getMessage(), 'completed') || str_contains($e->getMessage(), 'expired')
            ], 403);
        }
    }

    /**
     * Get exam questions (requires access)
     */
    public function questions(Request $request, Exam $exam)
    {
        $student = auth('student')->user();
        $gameId = $request->query('game') ?? $request->query('game_id');

        if (!$student) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }

        if (!$exam->hasAccessFor($student, $gameId)) {
            return response([
                'message' => __('messages.exam_purchase_required'),
                'needs_purchase' => true
            ], 402);
        }

        $questions = $exam->questions()->select([
            'id',
            'exam_id',
            'title',
            'type',
            'options',
            'q_options',
            'files',
            'duration',
            'points',
            'slide',
            'sequence_options'
        ])->get();

        // Ensure options are properly decoded for all questions
        $questions->each(function ($question) {
            if (is_string($question->options)) {
                $question->setAttribute('options', json_decode($question->options, true) ?? []);
            }
            if (is_string($question->q_options)) {
                $question->setAttribute('q_options', json_decode($question->q_options, true) ?? []);
            }
            if (is_string($question->sequence_options)) {
                $question->setAttribute('sequence_options', json_decode($question->sequence_options, true) ?? []);
            }
        });

        return response(['questions' => $questions]);
    }

    /**
     * Check exam access status
     */
    public function checkAccess(Request $request, Exam $exam)
    {
        $student = auth('student')->user();
        $gameId = $request->query('game') ?? $request->query('game_id');

        if (!$student) {
            return response([
                'has_access' => false,
                'message' => __('messages.unauthenticated')
            ], 401);
        }

        $hasAccess = $exam->hasAccessFor($student, $gameId);
        $isPurchased = $exam->isPurchasedBy($student->id, 'student');

        return response([
            'has_access' => $hasAccess,
            'is_purchased' => $isPurchased,
            'is_free' => $exam->monetize == 'free',
            'price' => $exam->price,
            'monetize' => $exam->monetize
        ]);
    }
}
