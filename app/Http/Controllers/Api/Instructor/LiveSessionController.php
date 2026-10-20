<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use App\Models\LiveSessionAnswer;
use App\Models\Question;
use App\Models\Option;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LiveSessionController extends Controller
{
    /**
     * Create a new live session
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:exams,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        
        $exam = Exam::findOrFail($request->exam_id);

        // Check if user has access to this exam
        if (!$exam->hasAccessFor($user)) {
            return response()->json(['message' => 'You do not have access to this exam'], 403);
        }

        // Create live session
        $session = LiveSession::create([
            'pin' => '', // Will be generated automatically
            'title' => $request->title,
            'description' => $request->description,
            'host_id' => $user->id,
            'host_type' => get_class($user),
            'exam_id' => $exam->id,
            'settings' => $request->settings ?? [],
        ]);

        // Create associated Game record so it appears in the reports page
        if (Schema::hasTable('games') && Schema::hasColumn('games', 'live_session_id')) {
            $ownerData = $session->host_type === \App\Models\Institute::class
                ? ['institute_id' => $session->host_id]
                : ['teacher_id' => $session->host_id];

            \App\Models\Game::firstOrCreate(
                ['live_session_id' => $session->id],
                array_merge($ownerData, [
                    'exam_id' => $exam->id,
                    'title' => $session->title ?: ("Live Session: " . ($exam->title ?? '')),
                    'status' => 'active',
                    'created_at' => $session->created_at ?? now(),
                    'updated_at' => now(),
                ])
            );
        }

        return response()->json([
            'session' => $session->load('exam.questions'),
            'pin' => $session->pin,
            'sessionId' => $session->id,
            'join_url' => url("/hostlive/{$session->pin}"),
            'qr_code_url' => url("/api/live-session/{$session->pin}/qr"),
        ]);
    }

    /**
     * Get live session details by PIN
     */
    public function getByPin($pin)
    {
        $session = LiveSession::with(['exam.questions', 'host'])
            ->where('pin', $pin)
            ->firstOrFail();

        return response()->json([
            'session' => $session,
            'participant_count' => $session->getParticipantCount(),
            'current_question' => $session->getCurrentQuestion(),
        ]);
    }

    /**
     * Get live session details by ID
     */
    public function getById($sessionId)
    {
        $session = LiveSession::with(['exam.questions', 'host'])
            ->findOrFail($sessionId);

        // Get participants
        $participants = $session->participants()
            ->where('status', '!=', 'disconnected')
            ->orderBy('current_rank', 'asc')
            ->get();

        $currentQuestion = $session->getCurrentQuestion();
        $currentAnswers = [];
        if ($currentQuestion) {
            $currentAnswers = $session->answers()
                ->where('question_id', $currentQuestion->id)
                ->with(['student'])
                ->get()
                ->map(function ($ans) use ($session) {
                    $participant = $session->participants()->where('student_id', $ans->student_id)->first();
                    return [
                        'participant_id' => $participant ? $participant->id : null,
                        'student_id' => $ans->student_id,
                        'name' => $participant ? $participant->getDisplayName() : ($ans->student->name ?? 'Student'),
                        'avatar' => $participant ? $participant->getDisplayAvatar() : ($ans->student->avatar ?? null),
                        'selected_option_id' => $ans->selected_option_id,
                        'answer_text' => $ans->answer_text,
                        'is_correct' => (bool)$ans->is_correct,
                        'points_earned' => $ans->points_earned,
                        'answer_time_ms' => $ans->answer_time_ms,
                    ];
                });
        }

        return response()->json([
            'session' => $session,
            'exam' => $session->exam, // Explicitly include exam with questions
            'participant_count' => $session->getParticipantCount(),
            'current_question' => $currentQuestion,
            'current_answers' => $currentAnswers,
            'participants' => $participants,
        ]);
    }

    /**
     * Join a live session (for students)
     */
    public function join(Request $request, $pin)
    {
        $session = LiveSession::where('pin', $pin)->first();

        if (!$session) {
            return response()->json(['message' => 'Live session not found'], 404);
        }

        if ($session->status === 'finished') {
            return response()->json(['message' => 'This session has ended'], 410);
        }

        // Get the authenticated user via Sanctum
        $user = Auth::user();
        
        // Must be authenticated (auth:sanctum already enforces this, but guard
        // here too) AND must be a Student — only students join as players.
        if (!$user) {
            return response()->json(['message' => 'Authentication required. Please login as a student first.'], 401);
        }
        if (!($user instanceof Student)) {
            // 403 (not 401) on purpose: the SPA treats every 401 as an expired
            // session and logs the user out. A logged-in teacher/institute who
            // scans a join QR should see a clear message, not get signed out.
            return response()->json([
                'message' => 'Live sessions can only be joined with a student account.',
            ], 403);
        }

        $student = $user;

        \Log::info('Student attempting to join live session', [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'session_pin' => $pin,
            'session_id' => $session->id,
        ]);

        // Check if student already joined
        $existingParticipant = LiveSessionParticipant::where('live_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingParticipant) {
            \Log::info('Student already joined, returning existing participant', [
                'participant_id' => $existingParticipant->id,
            ]);
            
            // ALWAYS broadcast even for rejoins so teacher sees the participant
            try {
                broadcast(new \App\Events\LiveSessionParticipantJoined($session, $existingParticipant));
                \Log::info('[BROADCAST-SUCCESS] Participant rejoin event broadcast', [
                    'session_pin' => $session->pin,
                    'participant_id' => $existingParticipant->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('[BROADCAST-ERROR] Failed to broadcast participant rejoin', [
                    'error' => $e->getMessage(),
                ]);
            }
            
            return response()->json([
                'message' => 'Already joined',
                'participant' => $existingParticipant,
                'session' => [
                    'id' => $session->id,
                    'pin' => $session->pin,
                    'status' => $session->status,
                    'exam_id' => $session->exam_id,
                    'config' => $session->config ?? (object)[], // Return empty object, not array
                    'current_question_index' => $session->current_question_index,
                    'started_at' => $session->started_at,
                    'exam' => $session->exam,
                ],
                'session_id' => $session->id,
                'exam_id' => $session->exam_id,
                'is_late_joiner' => $session->status !== 'waiting' && $session->current_question_index > 0,
                'should_wait' => $session->status === 'waiting',
                'redirect_url' => "/student/exam/{$session->exam_id}/play?live_pin={$session->pin}&live_session_id={$session->id}",
            ]);
        }

        // Create participant
        $participant = LiveSessionParticipant::create([
            'live_session_id' => $session->id,
            'student_id' => $student->id,
            'nickname' => $student->name,
            'avatar' => $student->avatar ?? '👤',
            'status' => 'joined',
            'joined_at' => now(),
        ]);

        // Broadcast participant joined with error handling
        try {
            broadcast(new \App\Events\LiveSessionParticipantJoined($session, $participant));
            \Log::info('[BROADCAST-SUCCESS] Participant joined event broadcast successful', [
                'session_pin' => $session->pin,
                'participant_id' => $participant->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('[BROADCAST-ERROR] Failed to broadcast participant joined', [
                'error' => $e->getMessage(),
                'session_pin' => $session->pin,
                'participant_id' => $participant->id,
            ]);
        }
        
        \Log::info('Student joined live session successfully', [
            'participant_id' => $participant->id,
            'student_id' => $student->id,
            'session_id' => $session->id,
            'exam_id' => $session->exam_id,
        ]);

        return response()->json([
            'participant' => $participant,
            'session' => [
                'id' => $session->id,
                'pin' => $session->pin,
                'status' => $session->status,
                'exam_id' => $session->exam_id,
                'config' => $session->config ?? (object)[], // Return empty object, not array
                'current_question_index' => $session->current_question_index,
                'started_at' => $session->started_at,
                'exam' => $session->exam,
            ],
            'session_id' => $session->id,
            'exam_id' => $session->exam_id,
            'is_late_joiner' => $session->status !== 'waiting' && $session->current_question_index > 0,
            'should_wait' => $session->status === 'waiting',
            'redirect_url' => "/student/exam/{$session->exam_id}/play?live_pin={$session->pin}&live_session_id={$session->id}",
        ]);
    }

    /**
     * Start the live session
     */
    public function start(Request $request, $sessionId)
    {
        $session = LiveSession::findOrFail($sessionId);
        
        // Check if user is the host
        $user = Auth::user();
        if ($session->host_id !== $user->id || $session->host_type !== get_class($user)) {
            return response()->json(['message' => 'Only the host can start the session'], 403);
        }

        if ($session->status !== 'waiting') {
            return response()->json(['message' => 'Session already started'], 400);
        }

        // Get the first question
        $firstQuestion = $session->exam->questions->first();
        $timeLimit = $request->get('time_limit', $firstQuestion->time ?? 30) * 1000; // Convert to milliseconds
        
        $startedAt = now();
        $session->update([
            'status' => 'question',
            'started_at' => $startedAt,
            'current_question_index' => 0,
            'current_question_started_at' => $startedAt,
            'current_question_time_limit' => $timeLimit,
        ]);

        // Broadcast session started with first question and timer sync
        broadcast(new \App\Events\LiveSessionStarted($session));
        broadcast(new \App\Events\LiveSessionQuestionStarted($session, $firstQuestion, $timeLimit));

        return response()->json([
            'session' => $session,
            'current_question' => $firstQuestion,
            'started_at' => $startedAt->toIso8601String(),
            'time_limit_ms' => $timeLimit,
        ]);
    }

    /**
     * Return current participants for a session (used by host UI to load initial list)
     */
    public function participants(Request $request, $sessionId)
    {
        try {
            \Log::info('[LiveSessionController] participants() called', [
                'sessionId' => $sessionId,
                'user' => auth()->user() ? ['id' => auth()->user()->id, 'type' => get_class(auth()->user())] : 'null',
            ]);

            $session = LiveSession::findOrFail($sessionId);

            $participants = LiveSessionParticipant::where('live_session_id', $session->id)
                ->with('student')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'live_session_id' => $p->live_session_id,
                        'student_id' => $p->student_id,
                        'nickname' => $p->nickname,
                        'avatar' => $p->avatar,
                        'total_score' => $p->total_score ?? 0,
                        'correct_answers' => $p->correct_answers ?? 0,
                        'current_rank' => $p->current_rank,
                        'average_answer_time' => $p->average_answer_time,
                        'status' => $p->status,
                        'student' => $p->student ? [
                            'id' => $p->student->id,
                            'name' => $p->student->name ?? '',
                            'avatar' => $p->student->avatar ?? '',
                        ] : null,
                    ];
                });

            \Log::info('[LiveSessionController] participants loaded', ['count' => $participants->count()]);

            return response()->json([
                'success' => true,
                'participants' => $participants,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('[LiveSessionController] Session not found', ['sessionId' => $sessionId]);
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
                'error' => 'Session ' . $sessionId . ' does not exist',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('[LiveSessionController] Error loading participants', [
                'sessionId' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error loading participants',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a question
     */
    public function startQuestion(Request $request, $sessionId)
    {
        $session = LiveSession::findOrFail($sessionId);
        
        // Check if user is the host
        $user = Auth::user();
        if ($session->host_id !== $user->id || $session->host_type !== get_class($user)) {
            return response()->json(['message' => 'Only the host can control the session'], 403);
        }

        $currentQuestion = $session->getCurrentQuestion();
        if (!$currentQuestion) {
            return response()->json(['message' => 'No more questions'], 400);
        }

        $timeLimit = $request->get('time_limit', $currentQuestion->time ?? 30) * 1000; // Convert to milliseconds
        $startedAt = now();

        $session->update([
            'status' => 'question',
            'current_question_started_at' => $startedAt,
            'current_question_time_limit' => $timeLimit,
        ]);

        // Broadcast question started with timer sync
        broadcast(new \App\Events\LiveSessionQuestionStarted($session, $currentQuestion, $timeLimit));

        return response()->json([
            'session' => $session,
            'question' => $currentQuestion,
            'started_at' => $startedAt->toIso8601String(),
            'time_limit_ms' => $timeLimit,
        ]);
    }

    /**
     * Move to previous question
     */
    public function previousQuestion(Request $request, $sessionId)
    {
        $session = LiveSession::findOrFail($sessionId);
        
        // Check if user is the host
        $user = Auth::user();
        if ($session->host_id !== $user->id || $session->host_type !== get_class($user)) {
            return response()->json(['message' => 'Only the host can control the session'], 403);
        }

        $prevIndex = $session->current_question_index - 1;
        
        if ($prevIndex < 0) {
            return response()->json(['message' => 'Already at first question'], 400);
        }
        
        // Validate that previous question exists
        $totalQuestions = $session->exam->questions->count();
        if ($prevIndex >= $totalQuestions) {
            return response()->json(['message' => 'Invalid question index'], 400);
        }

        // Get time limit from request or use question's time limit or default
        $previousQuestion = $session->exam->questions[$prevIndex];
        $timeLimit = $request->get('time_limit', $previousQuestion->time ?? $session->default_question_time ?? 30) * 1000;
        
        // Update session with new question and timer start
        $startedAt = now();
        $session->update([
            'current_question_index' => $prevIndex,
            'status' => 'question',
            'current_question_started_at' => $startedAt,
            'current_question_time_limit' => $timeLimit,
        ]);

        // Broadcast question navigation event with timer sync to all students
        try {
            broadcast(new \App\Events\LiveSessionQuestionPrevious($session, $previousQuestion));
            broadcast(new \App\Events\LiveSessionQuestionStarted($session, $previousQuestion, $timeLimit));
            
            \Log::info('[BROADCAST] LiveSessionQuestionPrevious + Timer sent', [
                'session_id' => $session->id,
                'pin' => $session->pin,
                'question_id' => $previousQuestion->id,
                'question_index' => $prevIndex,
                'started_at' => $startedAt->toIso8601String(),
                'time_limit_ms' => $timeLimit,
                'channel' => 'live-session.' . $session->pin,
            ]);
        } catch (\Exception $e) {
            \Log::error('[BROADCAST] Failed to broadcast LiveSessionQuestionPrevious', [
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'session' => $session->fresh(),
            'question' => $previousQuestion,
            'previous_question_available' => $prevIndex > 0,
            'next_question_available' => $prevIndex < ($totalQuestions - 1),
            'started_at' => $startedAt->toIso8601String(),
            'time_limit_ms' => $timeLimit,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Submit an answer
     */
    public function submitAnswer(Request $request, $sessionId)
    {
        \Log::info('[LIVE-ANSWER] Student submitting answer', [
            'session_id' => $sessionId,
            'question_id' => $request->question_id,
            'option_id' => $request->selected_option_id,
            'time_spent' => $request->time_spent,
        ]);
        
        // For sequence/ordinary questions, selected_option_id is optional (contains answer text)
        // For multiple_choice/true_false, selected_option_id is required (contains option ID)
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'nullable|string', // Made nullable for sequence/ordinary questions
            'time_spent' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            \Log::error('[LIVE-ANSWER] Validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $session = LiveSession::findOrFail($sessionId);
        
        // Get the authenticated user via Sanctum and verify they are a Student
        $user = Auth::user();
        if (!$user || !($user instanceof Student)) {
            \Log::warning('[LIVE-ANSWER] Non-student attempting to submit answer', ['user_id' => $user?->id]);
            return response()->json(['message' => 'Authentication required to submit answers'], 401);
        }
        $student = $user;

        // Check if session is in question mode
        if ($session->status !== 'question') {
            \Log::warning('[LIVE-ANSWER] Session not in question mode', [
                'session_id' => $sessionId,
                'status' => $session->status,
                'current_question_index' => $session->current_question_index,
            ]);
            
            // IMPORTANT: Allow final answer submission even if session is finished
            // This handles the case where teacher clicks Finish and student needs to submit their last answer
            // We'll validate the question is valid below
            if ($session->status === 'finished') {
                \Log::info('[LIVE-ANSWER] Allowing final answer submission for finished session');
                // Continue to allow answer submission
            }
            // Continue to allow answer submission for other statuses
        }
        
        $question = Question::findOrFail($request->question_id);
        
        // Validate that answer is not empty for questions that require it
        $selectedOptionId = $request->selected_option_id;
        if (empty($selectedOptionId) && in_array($question->type, ['multiple_choice', 'true_false'])) {
            \Log::error('[LIVE-ANSWER] Missing answer for multiple choice/true_false question', [
                'question_id' => $question->id,
                'question_type' => $question->type,
            ]);
            return response()->json([
                'message' => 'Answer is required',
                'errors' => ['selected_option_id' => ['Please select an answer before submitting']]
            ], 422);
        }
        
        // CRITICAL: Verify the question matches the current session question
        // Allow grace period for submitting answers when teacher moved to next question
        // This prevents "question no longer active" errors due to network latency
        $currentQuestion = $session->getCurrentQuestion();
        $questionsArray = $session->exam->questions;
        $isValidSubmission = false;
        
        // Check if submitting for current question
        if ($currentQuestion && $currentQuestion->id === $question->id) {
            $isValidSubmission = true;
            \Log::info('[LIVE-ANSWER] Submitting for current question', [
                'question_id' => $question->id,
            ]);
        }
        // Check if submitting for the immediately previous question (grace period for auto-submit)
        elseif ($session->current_question_index > 0) {
            $previousIndex = $session->current_question_index - 1;
            $previousQuestion = $questionsArray[$previousIndex] ?? null;
            if ($previousQuestion && $previousQuestion->id === $question->id) {
                $isValidSubmission = true;
                \Log::info('[LIVE-ANSWER] Submitting for previous question (auto-submit grace period)', [
                    'submitted_question_id' => $question->id,
                    'current_index' => $session->current_question_index,
                    'submitted_index' => $previousIndex,
                ]);
            }
        }
        
        if (!$isValidSubmission) {
            \Log::warning('[LIVE-ANSWER] Question mismatch - student answering old/invalid question', [
                'submitted_question_id' => $question->id,
                'current_question_id' => $currentQuestion?->id,
                'session_question_index' => $session->current_question_index,
            ]);
            return response()->json([
                'message' => 'This question is no longer active. The session has moved to a different question.',
                'current_question_id' => $currentQuestion?->id,
            ], 400);
        }
        
        // Check if student already answered this question
        $existingAnswer = LiveSessionAnswer::where('live_session_id', $session->id)
            ->where('student_id', $student->id)
            ->where('question_id', $request->question_id)
            ->first();

        if ($existingAnswer) {
            \Log::info('[LIVE-ANSWER] Student already answered this question', [
                'student_id' => $student->id,
                'question_id' => $request->question_id,
                'existing_answer_id' => $existingAnswer->id,
            ]);
            
            // Return the existing answer instead of error
            return response()->json([
                'message' => 'Already answered this question',
                'answer' => $existingAnswer,
                'is_correct' => $existingAnswer->is_correct,
                'points_earned' => $existingAnswer->points_earned,
                'already_answered' => true,
            ], 200); // Return 200 instead of 400
        }

        $selectedOptionId = $request->selected_option_id;
        
        \Log::info('[LIVE-ANSWER] Received answer submission', [
            'question_id' => $question->id,
            'question_type' => $question->type,
            'selected_option_id' => $selectedOptionId,
            'answer_length' => strlen($selectedOptionId ?? ''),
        ]);
        
        // Handle different question types
        $isCorrect = false;
        $selectedOption = null;
        $answerText = $selectedOptionId; // Store the actual answer text/value
        
        // For multiple choice/true-false questions with JSON options
        if (in_array($question->type, ['multiple_choice', 'true_false']) && !empty($question->options)) {
            $options = $question->options; // Returns array via accessor
            $selectedOption = collect($options)->firstWhere('id', $selectedOptionId);
            
            if (!$selectedOption) {
                \Log::error('[LIVE-ANSWER] Invalid option selected for multiple choice', [
                    'question_id' => $question->id,
                    'question_type' => $question->type,
                    'selected_option_id' => $selectedOptionId,
                    'available_options' => collect($options)->pluck('id')->toArray(),
                ]);
                return response()->json([
                    'message' => 'Invalid option selected',
                    'errors' => ['selected_option_id' => ['The selected option is invalid for this question']]
                ], 422);
            }
            
            $isCorrect = $selectedOption['correct'] ?? false;
            $answerText = $selectedOption['text'] ?? $selectedOptionId;
            // For these types, selected_option_id is valid as-is
        } else {
            // For ALL other question types: short_answer, long_answer, sequence, ordinary, etc.
            // The selected_option_id field actually contains the answer text/JSON
            // We need to store it as text, not as an option_id reference
            $answerText = $selectedOptionId;
            
            // Use QuestionService to check the answer
            try {
                $questionService = new \App\Services\QuestionService();
                $isCorrect = $questionService->checkAnswer($question->id, $selectedOptionId);
                
                \Log::info('[LIVE-ANSWER] Answer checked via QuestionService', [
                    'question_type' => $question->type,
                    'is_correct' => $isCorrect,
                ]);
            } catch (\Exception $e) {
                \Log::error('[LIVE-ANSWER] Error checking answer', [
                    'error' => $e->getMessage(),
                    'question_type' => $question->type,
                ]);
                // Default to incorrect if check fails
                $isCorrect = false;
            }
            
            // For non-multiple-choice questions, set selected_option_id to null
            // The answer is stored in the answer text field instead
            $selectedOptionId = null;
        }
        
        // Calculate answer time
        $answeredAt = now();
        $questionStartedAt = $session->current_question_started_at ?? now();
        $answerTimeMs = $request->filled('time_spent') && (int)$request->time_spent > 0
            ? (int) $request->time_spent * 1000
            : max(0, $answeredAt->diffInMilliseconds($questionStartedAt));
        $timeRemainingMs = max(0, ($session->current_question_time_limit ?? 30000) - $answerTimeMs);

        // Calculate points
        $pointsEarned = 0;
        if ($isCorrect) {
            $pointsEarned = $this->calculatePoints($answerTimeMs, $session->current_question_time_limit ?? 30000);
        }

        \Log::info('[LIVE-ANSWER] Processing answer', [
            'student_id' => $student->id,
            'question_id' => $question->id,
            'question_type' => $question->type,
            'selected_option_id' => $selectedOptionId,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'answer_time_ms' => $answerTimeMs,
        ]);

        // Create live session answer record
        $liveAnswer = LiveSessionAnswer::create([
            'live_session_id' => $session->id,
            'student_id' => $student->id,
            'question_id' => $question->id,
            'question_index' => $session->current_question_index,
            'selected_option_id' => $selectedOptionId, // null for non-multiple-choice
            'answer_text' => $answerText, // Store the actual answer
            'answered_at' => $answeredAt,
            'answer_time_ms' => $answerTimeMs,
            'time_remaining_ms' => $timeRemainingMs,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);

        // Get or create the game for this live session (for historical records)
        // Guard against older databases where games.live_session_id may not exist yet.
        $game = null;
        if (Schema::hasTable('games') && Schema::hasColumn('games', 'live_session_id')) {
            // Host is polymorphic: an institute owns the game via institute_id,
            // a teacher via teacher_id. Writing host_id into teacher_id for an
            // institute would corrupt ownership (and could violate the FK).
            $ownerData = $session->host_type === \App\Models\Institute::class
                ? ['institute_id' => $session->host_id]
                : ['teacher_id' => $session->host_id];

            $game = \App\Models\Game::firstOrCreate(
                [
                    'exam_id' => $session->exam_id,
                    'live_session_id' => $session->id,
                ],
                array_merge($ownerData, [
                    'title' => "Live Session: {$session->title}",
                    'status' => 'active',
                    'created_at' => $session->created_at,
                    'updated_at' => now(),
                ])
            );
        }

        // Also save to regular answers table for historical records
        if ($game) {
            \App\Models\Answer::create([
                'game_id' => $game->id,
                'student_id' => $student->id,
                'question_id' => $question->id,
                'selected_option_id' => $selectedOptionId,
                'answered_at' => $answeredAt,
                'answer_time' => $answerTimeMs / 1000, // Convert to seconds for regular table
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);
        }

        // Update participant stats
        $participant = LiveSessionParticipant::where('live_session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($participant) {
            $participant->addScore($pointsEarned);
            if ($isCorrect) {
                $participant->incrementCorrectAnswers();
            }
            $participant->updateAverageAnswerTime($answerTimeMs);
        }

        // Update rankings
        $this->updateRankings($session);

        // Broadcast answer submitted
        broadcast(new \App\Events\LiveSessionAnswerSubmitted($session, $liveAnswer, $participant));

        \Log::info('[LIVE-ANSWER] Answer recorded successfully', [
            'session_id' => $session->id,
            'student_id' => $student->id,
            'question_id' => $question->id,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);

        return response()->json([
            'answer' => $liveAnswer,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);
    }

    /**
     * Show question results and rankings
     */
    public function showResults(Request $request, $sessionId)
    {
        $session = LiveSession::findOrFail($sessionId);
        
        // Check if user is the host
        $user = Auth::user();
        if ($session->host_id !== $user->id || $session->host_type !== get_class($user)) {
            return response()->json(['message' => 'Only the host can show results'], 403);
        }

        $currentQuestion = $session->getCurrentQuestion();
        if (!$currentQuestion) {
            return response()->json(['message' => 'No current question'], 400);
        }

        // Get all answers for this question
        $answers = LiveSessionAnswer::where('live_session_id', $session->id)
            ->where('question_id', $currentQuestion->id)
            ->with(['student', 'selectedOption'])
            ->get();

        // Get current rankings
        $rankings = $session->getLeaderboard();

        // Broadcast results
        broadcast(new \App\Events\LiveSessionQuestionResults($session, $currentQuestion, $answers, $rankings));

        return response()->json([
            'answers' => $answers,
            'rankings' => $rankings,
            'correct_option' => $currentQuestion->options()->where('is_correct', true)->first(),
        ]);
    }

    /**
     * Move to next question
     */
    public function nextQuestion(Request $request, $sessionId)
    {
        $session = LiveSession::findOrFail($sessionId);
        
        $user = Auth::user();
        if ($session->host_id !== $user->id || $session->host_type !== get_class($user)) {
            return response()->json(['message' => 'Only the host can control the session'], 403);
        }

        $nextIndex = $session->current_question_index + 1;
        $totalQuestions = $session->exam->questions->count();
        
        if ($nextIndex >= $totalQuestions) {
            // End the session
            return $this->finish($request, $sessionId);
        }
        
        // Validate that next question exists
        if ($nextIndex < 0 || $nextIndex >= $totalQuestions) {
            return response()->json(['message' => 'Invalid question index'], 400);
        }

        // Get time limit from request or use question's time limit or default
        $nextQuestion = $session->exam->questions[$nextIndex];
        $timeLimit = $request->get('time_limit', $nextQuestion->time ?? $session->default_question_time ?? 30) * 1000;
        
        // Update session with new question and timer start
        $startedAt = now();
        $session->update([
            'current_question_index' => $nextIndex,
            'status' => 'question',
            'current_question_started_at' => $startedAt,
            'current_question_time_limit' => $timeLimit,
        ]);

        // Broadcast question navigation event with timer sync to all students
        try {
            broadcast(new \App\Events\LiveSessionQuestionNext($session, $nextQuestion));
            broadcast(new \App\Events\LiveSessionQuestionStarted($session, $nextQuestion, $timeLimit));
            
            \Log::info('[BROADCAST] LiveSessionQuestionNext + Timer sent', [
                'session_id' => $session->id,
                'pin' => $session->pin,
                'question_id' => $nextQuestion->id,
                'question_index' => $nextIndex,
                'started_at' => $startedAt->toIso8601String(),
                'time_limit_ms' => $timeLimit,
                'channel' => 'live-session.' . $session->pin,
            ]);
        } catch (\Exception $e) {
            \Log::error('[BROADCAST] Failed to broadcast LiveSessionQuestionNext', [
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'session' => $session->fresh(),
            'question' => $nextQuestion,
            'next_question_available' => $nextIndex < ($totalQuestions - 1),
            'previous_question_available' => $nextIndex > 0,
            'started_at' => $startedAt->toIso8601String(),
            'time_limit_ms' => $timeLimit,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Finish the live session
     */
    public function finish(Request $request, $sessionId)
    {
        $session = LiveSession::findOrFail($sessionId);
        
        // Check if user is the host
        $user = Auth::user();
        $isHost = ((int)$session->host_id === (int)$user->id) &&
            ($session->host_type === get_class($user) || $session->host_type === class_basename($user) || empty($session->host_type));
        if (!$isHost) {
            return response()->json(['message' => 'Only the host can finish the session'], 403);
        }

        $session->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        // Update final rankings
        try {
            $this->updateRankings($session);
        } catch (\Throwable $e) {
            \Log::warning('[LIVE-SESSION] Error updating rankings on finish: ' . $e->getMessage());
        }

        // Mark the associated game as completed safely
        try {
            if (Schema::hasTable('games') && Schema::hasColumn('games', 'live_session_id')) {
                $game = \App\Models\Game::where('live_session_id', $session->id)->first();
                if ($game) {
                    try {
                        $game->update([
                            'status' => 'completed',
                            'updated_at' => now(),
                        ]);
                    } catch (\Throwable $e) {
                        $game->update([
                            'status' => 'inactive',
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[LIVE-SESSION] Error syncing game status on finish: ' . $e->getMessage());
        }

        // Get final rankings
        $finalRankings = $session->getLeaderboard(50); // Top 50

        // Broadcast session finished safely
        try {
            broadcast(new \App\Events\LiveSessionFinished($session, $finalRankings));
        } catch (\Throwable $e) {
            \Log::warning('[LIVE-SESSION] Error broadcasting finish: ' . $e->getMessage());
        }

        return response()->json([
            'session' => $session,
            'final_rankings' => $finalRankings,
        ]);
    }

    /**
     * Get QR code for joining
     */
    public function getQrCode($pin)
    {
        $session = LiveSession::where('pin', $pin)->firstOrFail();
        // Generate a join URL for students
        // Frontend will use this to generate QR code with qrcode.js package
        $joinUrl = url("/hostlive/{$pin}");

        return response()->json([
            'join_url' => $joinUrl,
            'pin' => $pin,
            'message' => 'Use the join_url to generate QR code on frontend'
        ]);
    }
    
    /**
     * Get live session statistics and report
     */
    public function getSessionReport($sessionId)
    {
        if ($sessionId instanceof Request) {
            $sessionId = request()->route('sessionId') ?? $sessionId->get('sessionId');
        }
        $session = LiveSession::with(['exam.questions', 'participants.student', 'answers', 'host'])->findOrFail($sessionId);
        
        // Check if user has access
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isAuthorized = ((int)$session->host_id === (int)$user->id);
        if (!$isAuthorized) {
            if ($session->host_type === \App\Models\Teacher::class) {
                if ($user instanceof \App\Models\Teacher && (int)$user->id === (int)$session->host_id) {
                    $isAuthorized = true;
                } elseif (method_exists($user, 'teachers') && $user->teachers()->where('teachers.id', $session->host_id)->exists()) {
                    $isAuthorized = true;
                }
            } elseif ($session->host_type === \App\Models\Institute::class) {
                if ($user instanceof \App\Models\Institute && (int)$user->id === (int)$session->host_id) {
                    $isAuthorized = true;
                } elseif (isset($user->selected_institute) && (int)$user->selected_institute === (int)$session->host_id) {
                    $isAuthorized = true;
                }
            }
            if (!$isAuthorized && $session->exam) {
                if (($session->exam->teacher_id && (int)$session->exam->teacher_id === (int)$user->id) ||
                    ($session->exam->institute_id && (int)$session->exam->institute_id === (int)$user->id)) {
                    $isAuthorized = true;
                }
            }
            if (!$isAuthorized) {
                return response()->json(['message' => 'Access denied'], 403);
            }
        }
        
        $participants = $session->participants;
        $answers = $session->answers;
        $questions = $session->exam ? $session->exam->questions : collect();
        
        // Fallback: If relationship was empty or not populated, query LiveSessionAnswer directly
        if ($answers->isEmpty()) {
            $answers = \App\Models\LiveSessionAnswer::where('live_session_id', $session->id)->get();
        }

        // Second fallback: If still empty, check if answers were recorded in regular Answer attempts or Game
        if ($answers->isEmpty()) {
            $linkedGame = Schema::hasTable('games') && Schema::hasColumn('games', 'live_session_id')
                ? \App\Models\Game::where('live_session_id', $session->id)->first()
                : null;

            $attemptsQuery = \App\Models\Answer::query();
            if (Schema::hasColumn('answers', 'live_session_id')) {
                $attemptsQuery->where('live_session_id', $session->id);
            }
            if ($linkedGame) {
                $attemptsQuery->orWhere('game_id', $linkedGame->id);
            }
            $attempts = $attemptsQuery->get();

            if ($attempts->isNotEmpty()) {
                $syntheticAnswers = collect();
                foreach ($attempts as $attempt) {
                    foreach (($attempt->answers ?? []) as $a) {
                        $syntheticAnswers->push((object)[
                            'student_id' => $attempt->student_id,
                            'question_id' => $a['question_id'] ?? null,
                            'question_index' => null,
                            'selected_option_id' => is_numeric($a['answer'] ?? null) ? (int)$a['answer'] : null,
                            'answer_text' => is_string($a['answer'] ?? null) ? $a['answer'] : json_encode($a['answer'] ?? ''),
                            'is_correct' => (bool)($a['is_correct'] ?? false),
                            'points_earned' => ($a['is_correct'] ?? false) ? 1000 : 0,
                            'answer_time_ms' => ($a['time'] ?? 0) * 1000,
                        ]);
                    }
                }
                if ($syntheticAnswers->isNotEmpty()) {
                    $answers = $syntheticAnswers;
                }
            }
        }
        
        // Calculate statistics
        $totalParticipants = $participants->count();
        $totalQuestions = $questions->count();
        $totalAnswers = $answers->count();

        // Lookup of question_id => [option_id => option_text]
        $optionTextMap = [];
        $questionOrder = [];
        foreach ($questions->values() as $idx => $q) {
            $questionOrder[$q->id] = $idx;
            $map = [];
            $rawOptions = $q->getAttributes()['options'] ?? null;
            $opts = is_string($rawOptions) ? (json_decode($rawOptions, true) ?? []) : (is_array($q->options) ? $q->options : []);
            foreach ($opts as $optIdx => $opt) {
                $t = $opt['text'] ?? $opt['option_text'] ?? $opt['title'] ?? '';
                if (isset($opt['id'])) {
                    $map[(string) $opt['id']] = $t;
                }
                $map[(string) $optIdx] = $t;
            }
            $optionTextMap[$q->id] = $map;
        }

        // Participant performance
        $participantStats = $participants->map(function($participant) use ($answers, $totalQuestions, $optionTextMap, $questionOrder, $totalParticipants, $questions) {
            $participantAnswers = $answers->filter(function($a) use ($participant, $totalParticipants) {
                if ($participant->student_id && $a->student_id) {
                    return (int)$a->student_id === (int)$participant->student_id;
                }
                return $totalParticipants === 1;
            });
            $correctAnswers = $participantAnswers->where('is_correct', true)->count();
            if ($participant->correct_answers > $correctAnswers) {
                $correctAnswers = $participant->correct_answers;
            }
            $totalAnswered = $participantAnswers->count();
            $avgTime = $participantAnswers->avg('answer_time_ms');

            $studentAnswers = $participantAnswers
                ->sortBy(fn($a) => $questionOrder[$a->question_id] ?? $a->question_index ?? 0)
                ->map(function($a) use ($optionTextMap, $questions) {
                    $optText = null;
                    if ($a->selected_option_id !== null
                        && isset($optionTextMap[$a->question_id][(string) $a->selected_option_id])) {
                        $optText = $optionTextMap[$a->question_id][(string) $a->selected_option_id];
                    }
                    $answerVal = $optText !== null && $optText !== '' ? $optText : $a->answer_text;

                    // Fallback: If answerVal is empty but the answer was marked correct, look up the question's correct option
                    if (empty($answerVal) && $a->is_correct) {
                        $qObj = $questions->firstWhere('id', $a->question_id);
                        if ($qObj) {
                            $rawOpts = $qObj->getAttributes()['options'] ?? null;
                            $qOpts = is_string($rawOpts) ? (json_decode($rawOpts, true) ?? []) : (is_array($qObj->options) ? $qObj->options : []);
                            foreach ($qOpts as $o) {
                                if (!empty($o['correct'])) {
                                    $answerVal = $o['text'] ?? $o['option_text'] ?? $o['title'] ?? 'Correct';
                                    break;
                                }
                            }
                        }
                    }

                    if (empty($answerVal)) {
                        $answerVal = 'No Answer';
                    }

                    $durSec = round(($a->answer_time_ms ?? 0) / 1000, 1);
                    return [
                        'id' => $a->question_id,
                        'question_id' => $a->question_id,
                        'question_index' => $a->question_index,
                        'selected_option_id' => $a->selected_option_id,
                        'answer' => $answerVal,
                        'is_correct' => (bool) $a->is_correct,
                        'points_earned' => $a->points_earned ?? 0,
                        'answer_time_ms' => $a->answer_time_ms ?? 0,
                        'time' => $durSec,
                        'answered_duration' => $durSec,
                        'selected_option' => [
                            'title' => $answerVal,
                            'text' => $answerVal,
                            'correct' => (bool) $a->is_correct,
                            'score' => $a->points_earned ?? 0,
                            'answered_duration' => $durSec,
                        ],
                    ];
                })->values();

            $displayName = $participant->nickname ?: ($participant->student ? $participant->student->name : 'Player');
            $displayAvatar = $participant->avatar ?: ($participant->student ? $participant->student->avatar : null);
            $effectiveScore = max((int)$participant->total_score, (int)$participantAnswers->sum('points_earned'));
            $unansweredCount = max(0, $totalQuestions - $totalAnswered);

            return [
                'id' => $participant->student_id ?: $participant->id,
                'participant_id' => $participant->id,
                'student_id' => $participant->student_id,
                'name' => $displayName,
                'nickname' => $displayName,
                'avatar' => $displayAvatar,
                'image' => $displayAvatar,
                'total_score' => $effectiveScore,
                'score' => $effectiveScore,
                'correct_answers' => $correctAnswers,
                'total_answered' => $totalAnswered,
                'questions_remaining' => $unansweredCount,
                'unanswered_questions' => $unansweredCount,
                'accuracy' => $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : ($totalAnswered > 0 ? round(($correctAnswers / $totalAnswered) * 100, 1) : 0),
                'average_answer_time' => $avgTime ? round($avgTime / 1000, 1) : round(($participant->average_answer_time ?? 0) / 1000, 1),
                'current_rank' => $participant->current_rank,
                'rank' => $participant->current_rank,
                'is_completed' => true,
                'status' => 'completed',
                'answers' => $studentAnswers,
                'answered_questions' => $studentAnswers,
                'created_at' => $participant->created_at,
                'updated_at' => $participant->updated_at,
            ];
        })->sortByDesc('total_score')->values()->map(function($p, $i) {
            $p['rank'] = $i + 1;
            if (!$p['current_rank']) $p['current_rank'] = $i + 1;
            return $p;
        });
        
        // Question statistics
        $questionStats = $questions->map(function($question, $qIdx) use ($answers, $participantStats) {
            $questionAnswers = $answers->filter(function($a) use ($question) {
                return (int)$a->question_id === (int)$question->id;
            });
            $correctCount = $questionAnswers->where('is_correct', true)->count();
            $totalCount = $questionAnswers->count();
            
            // Option distribution
            $rawOptions = $question->getAttributes()['options'] ?? null;
            $opts = is_string($rawOptions) ? (json_decode($rawOptions, true) ?? []) : (is_array($question->options) ? $question->options : []);
            
            $formattedOptions = [];
            $optionDistribution = [];
            foreach ($opts as $optIdx => $option) {
                $optionId = $option['id'] ?? null;
                $optTitle = $option['text'] ?? $option['option_text'] ?? $option['title'] ?? '';
                $isOptCorrect = filter_var($option['correct'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $optShape = $option['shape'] ?? null;

                $count = $questionAnswers->filter(function($a) use ($optionId, $optIdx, $optTitle) {
                    if ($optionId !== null && (string)$a->selected_option_id === (string)$optionId) return true;
                    if ((string)$a->selected_option_id === (string)$optIdx) return true;
                    if (!empty($optTitle) && !empty($a->answer_text) && trim(strtolower((string)$a->answer_text)) === trim(strtolower((string)$optTitle))) return true;
                    return false;
                })->count();

                if ($count === 0 && $isOptCorrect) {
                    $correctWithoutExplicitOpt = $questionAnswers->filter(function($a) use ($opts) {
                        return (bool)$a->is_correct && ($a->selected_option_id === null || !collect($opts)->contains('id', $a->selected_option_id));
                    })->count();
                    $count += $correctWithoutExplicitOpt;
                }

                $percent = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;

                $formattedOptions[] = [
                    'id' => $optionId ?? $optIdx,
                    'title' => $optTitle,
                    'text' => $optTitle,
                    'correct' => $isOptCorrect,
                    'shape' => $optShape,
                    'count' => $count,
                    'submitted_answers' => $count,
                    'percentage' => $percent,
                ];

                $optionDistribution[] = [
                    'option_id' => $optionId ?? $optIdx,
                    'id' => $optionId ?? $optIdx,
                    'option_text' => $optTitle,
                    'text' => $optTitle,
                    'title' => $optTitle,
                    'is_correct' => $isOptCorrect,
                    'correct' => $isOptCorrect,
                    'shape' => $optShape,
                    'count' => $count,
                    'submitted_answers' => $count,
                    'percentage' => $percent,
                ];
            }

            $tuteeAnswersList = $participantStats->map(function ($player) use ($question) {
                $ans = collect($player['answered_questions'])->firstWhere('question_id', $question->id);
                if (!$ans) return null;
                return [
                    'playerId' => $player['id'],
                    'id' => $player['id'],
                    'name' => $player['name'],
                    'image' => $player['image'],
                    'selected_option' => $ans['selected_option'] ?? [
                        'title' => $ans['answer'] ?? 'No Answer',
                        'correct' => (bool)($ans['is_correct'] ?? false),
                        'score' => $ans['points_earned'] ?? 0,
                        'answered_duration' => $ans['time'] ?? 0,
                    ]
                ];
            })->filter()->values();
            
            $avgDur = $questionAnswers->avg('answer_time_ms') ? round($questionAnswers->avg('answer_time_ms') / 1000, 1) : ($question->duration ?: 30);

            return [
                'id' => $question->id,
                'question_id' => $question->id,
                'title' => $question->title,
                'question_text' => $question->title,
                'type' => $question->type,
                'question_type' => $question->type,
                'order' => $qIdx + 1,
                'duration' => $question->duration ?: 30,
                'picture' => $question->slide,
                'slide' => $question->slide,
                'total_answers' => $totalCount,
                'correct_answers' => $correctCount,
                'incorrect_answers' => max(0, $totalCount - $correctCount),
                'accuracy' => $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 1) : 0,
                'average_time' => $avgDur,
                'avg_duration' => $avgDur,
                'options' => $formattedOptions,
                'option_distribution' => $optionDistribution,
                'tutees_answers' => $tuteeAnswersList,
            ];
        })->values();
        
        // Session timing
        $sessionDuration = null;
        if ($session->started_at && $session->finished_at) {
            $diff = $session->started_at->diffInSeconds($session->finished_at);
            if ($diff > 0 && $diff <= 21600) {
                $sessionDuration = $diff;
            }
        }

        if (!$sessionDuration && $answers->isNotEmpty()) {
            $firstAnswer = $answers->min('created_at') ?? $answers->min('answered_at');
            $lastAnswer = $answers->max('created_at') ?? $answers->max('answered_at');
            if ($firstAnswer && $lastAnswer) {
                try {
                    $span = \Carbon\Carbon::parse($firstAnswer)->diffInSeconds(\Carbon\Carbon::parse($lastAnswer));
                    if ($span > 0 && $span <= 21600) {
                        $sessionDuration = $span + 30;
                    }
                } catch (\Exception $e) {}
            }
        }

        if (!$sessionDuration || $sessionDuration > 21600) {
            $qDurSum = $questions->sum('duration');
            $sessionDuration = $qDurSum > 0 ? $qDurSum : max(60, $totalQuestions * 45);
        }
        
        // Overall statistics
        $overallStats = [
            'total_participants' => $totalParticipants,
            'total_questions' => $totalQuestions,
            'total_answers_submitted' => $totalAnswers,
            'average_score' => $participants->avg('total_score') ? round($participants->avg('total_score')) : 0,
            'highest_score' => $participants->max('total_score') ?? 0,
            'lowest_score' => $participants->min('total_score') ?? 0,
            'completion_rate' => ($totalParticipants > 0 && $totalQuestions > 0) ? round(($totalAnswers / ($totalParticipants * $totalQuestions)) * 100, 1) : 0,
            'session_duration_seconds' => $sessionDuration,
            'session_duration_formatted' => $sessionDuration ? gmdate('H:i:s', $sessionDuration) : null,
        ];

        $hostName = $session->host ? ($session->host->name ?? 'Host') : 'Host';
        $examTitle = $session->exam ? $session->exam->title : ($session->title ?: 'Live Host Exam');
        $finishedDate = $session->finished_at ? $session->finished_at->toDateTimeString() : ($session->started_at ? $session->started_at->toDateTimeString() : now()->toDateTimeString());
        
        $notFinished = $participantStats->filter(fn($p) => ($p['unanswered_questions'] ?? 0) > 0)->values();
        $helpNeeders = $participantStats->filter(fn($p) => ($p['accuracy'] ?? 0) < 35)->values();
        $difficultQuestions = $questionStats->filter(fn($q) => $q['type'] !== 'slide' && $q['total_answers'] > 0 && ($q['correct_answers'] / $q['total_answers']) < 0.35)->values();

        $avgScore = $totalQuestions > 0 && $totalParticipants > 0
            ? round(($participantStats->sum('correct_answers') / ($totalQuestions * $totalParticipants)) * 100, 1)
            : 0;

        return response()->json([
            'id' => $session->id,
            'title' => $session->title ?: $examTitle,
            'host' => $hostName,
            'end_at' => $finishedDate,
            'finished_at' => $finishedDate,
            'status' => 'finished',
            'duration' => $sessionDuration ?: $questions->sum('duration'),
            'avg_score' => $avgScore,
            'session' => [
                'id' => $session->id,
                'pin' => $session->pin,
                'title' => $session->title ?: $examTitle,
                'status' => 'finished',
                'started_at' => $session->started_at,
                'finished_at' => $session->finished_at,
                'exam_title' => $examTitle,
            ],
            'overall_stats' => $overallStats,
            'participant_stats' => $participantStats,
            'players' => $participantStats,
            'question_stats' => $questionStats,
            'questions' => $questionStats,
            'leaderboard' => $participantStats->take(10),
            'difficult_questions' => $difficultQuestions,
            'help_needers' => $helpNeeders,
            'not_finished' => $notFinished,
            'groups' => collect(),
        ]);
    }

    /**
     * Calculate points based on answer time
     */
    private function calculatePoints($answerTimeMs, $timeLimitMs, $basePoints = 1000)
    {
        if ($answerTimeMs >= $timeLimitMs) {
            return $basePoints; // Minimum points for correct answer
        }

        // Bonus points for answering quickly
        $timeBonus = max(0, ($timeLimitMs - $answerTimeMs) / $timeLimitMs) * 500;
        
        return (int) ($basePoints + $timeBonus);
    }

    /**
     * Handle timer pause/resume broadcast to students
     */
    public function timerPause(Request $request, $sessionId)
    {
        \Log::info('[TIMER-PAUSE] Timer pause/resume request', [
            'session_id' => $sessionId,
            'paused' => $request->paused,
            'remaining_seconds' => $request->remaining_seconds,
        ]);

        $session = LiveSession::findOrFail($sessionId);
        
        // Validate that the user is the host (teacher)
        $user = Auth::user();
        if (!$user || $session->host_id !== $user->id) {
            \Log::warning('[TIMER-PAUSE] Unauthorized attempt to control timer', [
                'user_id' => $user?->id,
                'host_id' => $session->host_id,
            ]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Broadcast timer pause/resume event to all students in the session
        try {
            broadcast(new \App\Events\TimerPauseToggled(
                $session,
                $request->paused ?? false,
                $request->remaining_seconds ?? 0
            ))->toOthers();

            \Log::info('[TIMER-PAUSE] Broadcasted to students', [
                'session_id' => $sessionId,
                'paused' => $request->paused,
            ]);

            return response()->json([
                'message' => $request->paused ? 'Timer paused' : 'Timer resumed',
                'paused' => $request->paused,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('[TIMER-PAUSE] Broadcast failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            return response()->json(['message' => 'Failed to broadcast timer state'], 500);
        }
    }

    /**
     * Update session configuration (auto_advance, teacher_paced, show_results)
     */
    public function updateConfig(Request $request, $sessionId)
    {
        \Log::info('[CONFIG-UPDATE] Session config update request', [
            'session_id' => $sessionId,
            'config' => $request->all(),
        ]);

        $session = LiveSession::findOrFail($sessionId);
        
        // Validate that the user is the host (teacher)
        $user = Auth::user();
        if (!$user || $session->host_id !== $user->id) {
            \Log::warning('[CONFIG-UPDATE] Unauthorized attempt to update config', [
                'user_id' => $user?->id,
                'host_id' => $session->host_id,
            ]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update the config
        try {
            $config = $session->config ?? [];
            
            if ($request->has('title')) {
                $session->title = $request->title;
                if (Schema::hasTable('games') && Schema::hasColumn('games', 'live_session_id')) {
                    \App\Models\Game::where('live_session_id', $session->id)->update(['title' => $request->title]);
                }
            }
            if ($request->has('auto_advance')) {
                $config['auto_advance'] = (bool) $request->auto_advance;
            }
            if ($request->has('teacher_paced')) {
                $config['teacher_paced'] = (bool) $request->teacher_paced;
            }
            if ($request->has('show_results')) {
                $config['show_results'] = (bool) $request->show_results;
            }
            
            $session->config = $config;
            $session->save();

            \Log::info('[CONFIG-UPDATE] Config updated successfully', [
                'session_id' => $sessionId,
                'new_config' => $config,
            ]);

            // Broadcast config update event to all students in the session
            try {
                broadcast(new \App\Events\ConfigUpdated($session, $config))->toOthers();
                
                \Log::info('[CONFIG-UPDATE] Broadcasted to students', [
                    'session_id' => $sessionId,
                    'config' => $config,
                ]);
            } catch (\Exception $broadcastErr) {
                \Log::warning('[CONFIG-UPDATE] Broadcast failed but config was updated', [
                    'error' => $broadcastErr->getMessage(),
                    'session_id' => $sessionId,
                ]);
                // Don't fail the request - config is updated even if broadcast fails
            }

            return response()->json([
                'message' => 'Configuration updated successfully',
                'config' => $config,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('[CONFIG-UPDATE] Failed to update config', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            return response()->json(['message' => 'Failed to update configuration'], 500);
        }
    }

    /**
     * Update participant rankings
     */
    private function updateRankings($session)
    {
        $participants = LiveSessionParticipant::where('live_session_id', $session->id)
            ->where('status', '!=', 'disconnected')
            ->orderBy('total_score', 'desc')
            ->orderBy('total_answer_time', 'asc')
            ->get();

        foreach ($participants as $index => $participant) {
            $participant->updateRank($index + 1);
        }
    }
}
