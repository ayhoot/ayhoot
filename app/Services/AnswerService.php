<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use App\Models\Game;
use App\Models\LiveSession;
use App\Models\LiveSessionParticipant;
use App\Events\LiveSessionAnswerSubmitted;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnswerService
{
    protected $questionService;

    public function __construct()
    {
        $this->questionService = new QuestionService();
    }

    /**
     * Start exam attempt
     */
    public function startExamAttempt($student, $exam, $game = null)
    {
        try {
            // Check if student has access
            if (!$exam->hasAccessFor($student, $game?->id)) {
                throw new \Exception('Access denied to exam');
            }

            // Check for existing attempt
            $existingAttempt = Answer::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->where('game_id', $game?->id)
                ->latest('id')
                ->first();

            if ($existingAttempt) {
                if ($existingAttempt->status == 'completed') {
                    // Allow re-attempts when not part of an assigned game (practice mode)
                    if ($game == null) {
                        $totalDuration = $exam->questions->sum(fn($q) => $q->duration ?: 30);

                        $attempt = Answer::create([
                            'student_id' => $student->id,
                            'exam_id' => $exam->id,
                            'game_id' => null,
                            'answers' => [],
                            'started_at' => Carbon::now(),
                            'status' => 'started',
                            'total_duration_seconds' => $totalDuration,
                        ]);

                        return $attempt;
                    }

                    throw new \Exception('Exam attempt already completed');
                }

                // Check if time expired
                if ($existingAttempt->remaining_time <= 0) {
                    $existingAttempt->status = 'completed';
                    $existingAttempt->completed_at = Carbon::now();
                    $existingAttempt->save();
                    throw new \Exception('Exam time expired');
                }

                return $existingAttempt;
            }

            // Create new attempt
            $totalDuration = $exam->questions->sum(fn($q) => $q->duration ?: 30);

            $attempt = Answer::create([
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'game_id' => $game?->id,
                'answers' => [],
                'started_at' => Carbon::now(),
                'status' => 'started',
                'total_duration_seconds' => $totalDuration,
            ]);

            return $attempt;
        } catch (\Exception $e) {
            Log::error('Failed to start exam attempt: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save student answers
     */
    public function saveAnswers($attemptId, $answers, $student, $isLiveSession = false, $liveSessionId = null)
    {
        try {
            $attempt = Answer::where('id', $attemptId)
                ->where('student_id', $student->id)
                ->first();

            if (!$attempt) {
                throw new \Exception('Invalid attempt or access denied');
            }

            // Update attempt with live session info if provided
            if ($isLiveSession && $liveSessionId) {
                $attempt->is_live_session = true;
                $attempt->live_session_id = $liveSessionId;
            }

            if ($attempt->status == 'completed') {
                throw new \Exception('Exam attempt already completed');
            }

            if ($attempt->remaining_time <= 0) {
                $attempt->status = 'completed';
                $attempt->completed_at = Carbon::now();
                $attempt->save();
                throw new \Exception('Exam time expired');
            }

            // Process answers
            $existingAnswers = collect($attempt->answers ?? [])->keyBy('question_id');
            $results = [];

            $submittedQuestionIds = collect($answers)->pluck('question_id')->unique()->filter();
            $questions = Question::whereIn('id', $submittedQuestionIds)->get()->keyBy('id');

            foreach ($answers as $answerData) {
                $questionId = (int)($answerData['question_id'] ?? 0);
                if ($questionId == 0) continue;

                $studentAnswer = $answerData['answer'] ?? '';
                $question = $questions->get($questionId);
                $timeSpent = (int)($answerData['time'] ?? 0);
                $files = $answerData['files'] ?? [];
                $isTimedOut = $answerData['timed_out'] ?? false;

                $isCorrect = false;
                $pointsEarned = 0;
                if ($question) {
                    $isCorrect = $this->questionService->checkAnswer($questionId, $studentAnswer);
                    // Calculate points for live session
                    if ($isLiveSession && $isCorrect) {
                        $pointsEarned = 1000; // Base points, can be adjusted based on time
                    }
                }

                $existingAnswers->put($questionId, [
                    'question_id' => $questionId,
                    'answer' => $studentAnswer,
                    'files' => $files,
                    'is_correct' => $isCorrect,
                    'time' => $timeSpent,
                    'question_timed_out' => $isTimedOut,
                ]);

                $results[] = [
                    'question_id' => $questionId,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned
                ];

                // Broadcast to live session if this is part of a live exam
                if ($isLiveSession && $liveSessionId) {
                    $this->broadcastLiveSessionAnswer($liveSessionId, $student, $question, $studentAnswer, $isCorrect, $pointsEarned, $timeSpent);
                } else {
                    $this->broadcastAnswerToLiveSession($attempt, $question, $studentAnswer, $isCorrect, $student);
                }
            }

            $attempt->answers = $existingAnswers->values()->all();

            // Check if all questions are answered
            $this->checkCompletionStatus($attempt);
            $attempt->save();

            return [
                'success' => true,
                'results' => $results,
                'attempt_status' => $attempt->status,
                'remaining_time' => $attempt->remaining_time
            ];
        } catch (\Exception $e) {
            Log::error('Failed to save answers: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Broadcast answer to live session if applicable
     */
    protected function broadcastAnswerToLiveSession($attempt, $question, $answer, $isCorrect, $student)
    {
        try {
            // Check if there's an active live session for this exam
            $liveSession = LiveSession::where('exam_id', $attempt->exam_id)
                ->where('status', 'question')
                ->first();

            if (!$liveSession) {
                Log::info('No active live session for exam', ['exam_id' => $attempt->exam_id]);
                return;
            }

            // Get participant
            $participant = LiveSessionParticipant::where('live_session_id', $liveSession->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$participant) {
                Log::warning('Participant not found in live session', [
                    'session_id' => $liveSession->id,
                    'student_id' => $student->id,
                ]);
                return;
            }

            // Ensure question has options loaded
            if (!$question->relationLoaded('options')) {
                $question->load('options');
            }

            // Parse answer to get selected option ID
            $selectedOptionId = null;
            if ($question && is_numeric($answer)) {
                $selectedOptionId = (int)$answer;
            } elseif ($question && $question->options) {
                // Convert options to collection if it's an array
                $optionsCollection = is_array($question->options) ? collect($question->options) : $question->options;
                
                // Try to find matching option by text
                $matchingOption = $optionsCollection->first(function ($opt) use ($answer) {
                    $optText = is_array($opt) ? ($opt['text'] ?? $opt['option_text'] ?? $opt['title'] ?? '') : ($opt->text ?? $opt->option_text ?? $opt->title ?? '');
                    return $optText === $answer;
                });
                
                if ($matchingOption) {
                    $selectedOptionId = is_array($matchingOption) ? ($matchingOption['id'] ?? null) : ($matchingOption->id ?? null);
                }
            }

            $answerText = is_string($answer) ? $answer : (is_array($answer) ? json_encode($answer) : (string)$answer);
            // Create live session answer record
            $liveAnswer = \App\Models\LiveSessionAnswer::create([
                'live_session_id' => $liveSession->id,
                'student_id' => $student->id,
                'question_id' => $question->id,
                'question_index' => $liveSession->current_question_index,
                'selected_option_id' => $selectedOptionId,
                'answer_text' => $answerText,
                'answered_at' => now(),
                'answer_time_ms' => isset($answerData['time']) ? (int)($answerData['time'] * 1000) : 0,
                'time_remaining_ms' => 0,
                'is_correct' => $isCorrect,
                'points_earned' => $isCorrect ? 1000 : 0,
            ]);

            // Update participant stats
            if ($isCorrect) {
                $participant->increment('correct_answers');
                $participant->increment('total_score', 1000);
            }

            // Broadcast the answer event
            broadcast(new LiveSessionAnswerSubmitted($liveSession, $liveAnswer, $participant));

            Log::info('Broadcasted answer to live session', [
                'session_id' => $liveSession->id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'question_id' => $question->id,
                'selected_option_id' => $selectedOptionId,
                'is_correct' => $isCorrect,
                'participant_score' => $participant->total_score,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast answer to live session: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Broadcast answer to live session (unified system)
     */
    protected function broadcastLiveSessionAnswer($liveSessionId, $student, $question, $answer, $isCorrect, $pointsEarned, $timeSpent)
    {
        try {
            $liveSession = LiveSession::find($liveSessionId);
            if (!$liveSession) {
                Log::warning('Live session not found', ['session_id' => $liveSessionId]);
                return;
            }

            // Get participant
            $participant = LiveSessionParticipant::where('live_session_id', $liveSession->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$participant) {
                Log::warning('Participant not found in live session', [
                    'session_id' => $liveSession->id,
                    'student_id' => $student->id,
                ]);
                return;
            }

            // Update participant stats
            if ($isCorrect) {
                $participant->increment('correct_answers');
                $participant->increment('total_score', $pointsEarned);
            }

            // Create or update LiveSessionAnswer record
            $selectedOptionId = is_numeric($answer) ? (int)$answer : null;
            $liveAnswer = \App\Models\LiveSessionAnswer::firstOrCreate([
                'live_session_id' => $liveSession->id,
                'student_id' => $student->id,
                'question_id' => $question->id,
            ], [
                'question_index' => $liveSession->current_question_index ?? 0,
                'selected_option_id' => $selectedOptionId,
                'answer_text' => is_string($answer) ? $answer : json_encode($answer),
                'answered_at' => now(),
                'answer_time_ms' => $timeSpent * 1000,
                'time_remaining_ms' => 0,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);

            // Broadcast answer submitted event to host
            broadcast(new LiveSessionAnswerSubmitted($liveSession, $liveAnswer, $participant));
            
            Log::info('[UNIFIED] Updated participant stats and created live answer for live session', [
                'session_id' => $liveSession->id,
                'student_id' => $student->id,
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'participant_score' => $participant->total_score,
            ]);
        } catch (\Exception $e) {
            Log::warning('[UNIFIED] Failed to update participant stats: ' . $e->getMessage());
        }
    }

    /**
     * Get student answer for a question
     */
    public function getStudentAnswer($student, $exam, $question, $game = null)
    {
        try {
            $answerRecord = Answer::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->where('game_id', $game?->id)
                ->first();

            if (!$answerRecord) {
                return null;
            }

            $studentAnswer = collect($answerRecord->answers ?? [])
                ->firstWhere('question_id', (int)$question->id);

            return $studentAnswer ? [
                'question_id' => $question->id,
                'answer' => $studentAnswer['answer'] ?? null,
                'is_correct' => $studentAnswer['is_correct'] ?? null,
                'files' => $studentAnswer['files'] ?? [],
                'time' => $studentAnswer['time'] ?? 0,
            ] : null;
        } catch (\Exception $e) {
            Log::error('Failed to get student answer: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate comprehensive exam report
     */
    public function generateExamReport($answerId, $student)
    {
        try {
            $answer = Answer::where('id', $answerId)
                ->where('student_id', $student->id)
                ->with([
                    'exam' => function ($query) {
                        $query->with([
                            'questions',
                            'institute:id,name',
                            'teacher:id,first_name,last_name'
                        ]);
                    }
                ])
                ->first();

            if (!$answer || !$answer->exam) {
                throw new \Exception('Exam report not found');
            }

            return $this->buildComprehensiveReport($answer, $student);
        } catch (\Exception $e) {
            Log::error('Failed to generate exam report: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get student's attended exams
     */
    public function getAttendedExams($student, $filters = [])
    {
        try {
            $query = Exam::with(['category', 'institute:id,name', 'teacher:id,first_name,last_name'])
                ->whereHas('answers', function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->with(['answers' => function ($q) use ($student) {
                    $q->where('student_id', $student->id)
                        ->select([
                            'id',
                            'exam_id',
                            'student_id',
                            'game_id',
                            'status',
                            'started_at',
                            'completed_at',
                            'answers'
                        ]);
                }]);

            // Apply filters
            if (!empty($filters['search'])) {
                $query->where('title', 'like', '%' . $filters['search'] . '%');
            }

            $exams = $query->paginate($filters['per_page'] ?? 10);

            // Transform results
            foreach ($exams->items() as $exam) {
                $attempt = $exam->answers->first();
                if ($attempt) {
                    $exam->student_attempt = [
                        'id' => $attempt->id,
                        'status' => $attempt->status,
                        'started_at' => $attempt->started_at?->toIso8601String(),
                        'completed_at' => $attempt->completed_at?->toIso8601String(),
                        'game_id' => $attempt->game_id,
                        'score' => $this->calculateScore($attempt->answers, $exam->questions),
                        'remaining_time' => $attempt->remaining_time
                    ];
                } else {
                    $exam->student_attempt = null;
                }
                unset($exam->answers);
            }

            return $exams;
        } catch (\Exception $e) {
            Log::error('Failed to get attended exams: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate score from answers
     */
    public function calculateScore($answers, $questions)
    {
        if (empty($answers) || $questions->isEmpty()) {
            return 0;
        }

        $totalPoints = 0;
        $earnedPoints = 0;
        $questionsKeyed = $questions->keyBy('id');

        foreach ($answers as $answer) {
            $question = $questionsKeyed->get($answer['question_id']);
            if ($question && $question->type !== 'slide') {
                $questionPoints = $question->points ?? 1; // Default points if not set
                $totalPoints += $questionPoints;
                if ($answer['is_correct'] ?? false) {
                    $earnedPoints += $questionPoints;
                }
            }
        }

        return $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    }

    /**
     * Check if exam attempt should be marked as completed
     */
    private function checkCompletionStatus($attempt)
    {
        $totalQuestions = $attempt->exam->questions()->count();
        $answeredCount = count($attempt->answers);

        if ($answeredCount >= $totalQuestions) {
            $nonSlideQuestions = $attempt->exam->questions()->where('type', '!=', 'slide')->count();
            $answeredNonSlide = collect($attempt->answers)->filter(function ($answer) use ($attempt) {
                $question = $attempt->exam->questions()->find($answer['question_id']);
                return $question && $question->type !== 'slide';
            })->count();

            if ($answeredNonSlide >= $nonSlideQuestions && $nonSlideQuestions > 0) {
                $attempt->status = 'completed';
                $attempt->completed_at = Carbon::now();
            }
        }
    }

private function buildComprehensiveReport($answer, $student)
{
    $exam = $answer->exam;
    $studentAnswers = $answer->answers ?? [];
    
    // 1. Calculate basic stats for the current student
    $stats = $this->calculateComprehensiveStats($studentAnswers, $exam->questions);
    
    $rankInfo = null;
    $players = collect();
    $questionsData = collect();
    $questions = $exam->questions;

    // 2. Handle Game-Specific Data (Rankings, Groups, All Players)
    if (!empty($answer->game_id)) {
        try {
            $game = \App\Models\Game::with(['groups.students'])->find($answer->game_id);
            
            // Get ALL completed attempts for this game to build the leaderboard and players list
            $allGameAttempts = \App\Models\Answer::where('game_id', $answer->game_id)
                ->where('status', 'completed')
                ->with('student')
                ->get();

            // Calculate scores for everyone once
            $allScores = $allGameAttempts->map(function ($att) use ($questions) {
                $score = 0;
                $correctCount = 0;
                $attAnswers = collect($att->answers ?? []);
                
                foreach ($attAnswers as $sa) {
                    $q = $questions->firstWhere('id', $sa['question_id']);
                    if ($q && $q->type !== 'slide') {
                        $isCorrect = $sa['is_correct'] ?? false;
                        if ($isCorrect) {
                            $correctCount++;
                            $score += ($q->points ?? 1);
                        }
                    }
                }

                return [
                    'attempt_id' => $att->id,
                    'student_id' => $att->student_id,
                    'name' => $att->student->name ?? 'Student',
                    'image' => $att->student->avatar ?? null,
                    'score' => $score,
                    'correct_answers' => $correctCount,
                    'unanswered_questions' => $questions->where('type', '!=', 'slide')->count() - $attAnswers->filter(function($a) use ($questions) {
                        $q = $questions->firstWhere('id', $a['question_id'] ?? null);
                        return $q && $q->type !== 'slide';
                    })->count(),
                    'completed_at' => $att->completed_at?->timestamp ?? 0,
                    'answered_questions' => $attAnswers
                ];
            });

            // Sort globally: Score DESC, Time ASC (First to finish wins tie)
            $sortedGlobal = $allScores->sort(function ($a, $b) {
                if ($a['score'] === $b['score']) {
                    return $a['completed_at'] <=> $b['completed_at'];
                }
                return $b['score'] <=> $a['score'];
            })->values();

            // Global Rank for current student
            $myGlobalRank = $sortedGlobal->search(fn($item) => $item['attempt_id'] == $answer->id) + 1;

            $rankInfo = [
                'rank' => $myGlobalRank,
                'total_players' => $sortedGlobal->count(),
                'leaderboard' => $sortedGlobal->take(10)->map(fn($row) => [
                    'student_id' => $row['student_id'],
                    'name' => $row['name'],
                    'score' => $row['score'],
                    'image' => $row['image']
                ])->values(),
                'group_ranks' => []
            ];

            // Calculate Group Ranks if groups exist
            if ($game && $game->groups->isNotEmpty()) {
                foreach ($game->groups as $group) {
                    $groupStudentIds = $group->students->pluck('id')->toArray();
                    
                    // Only process this group if the current student is IN it
                    if (in_array($answer->student_id, $groupStudentIds)) {
                        $groupScores = $sortedGlobal->filter(fn($s) => in_array($s['student_id'], $groupStudentIds))->values();
                        
                        $myGroupRank = $groupScores->search(fn($item) => $item['attempt_id'] == $answer->id) + 1;

                        $rankInfo['group_ranks'][] = [
                            'group_id' => $group->id,
                            'group_name' => $group->title ?? $group->name,
                            'rank' => $myGroupRank,
                            'total_players' => $groupScores->count(),
                            'leaderboard' => $groupScores->take(10)->map(fn($row) => [
                                'student_id' => $row['student_id'],
                                'name' => $row['name'],
                                'score' => $row['score'],
                                'image' => $row['image']
                            ])->values()
                        ];
                    }
                }
            }

            // Set players list to ALL attendants
            $players = $sortedGlobal->map(function($p, $index) {
                $p['rank'] = $index + 1;
                return $p;
            });

            // Build Comprehensive Questions Data for the Game
            $allAnswersFlat = $allGameAttempts->pluck('answers')->flatten(1);
            $questionsData = $questions->map(function ($q) use ($allAnswersFlat, $allGameAttempts) {
                $qAnswers = $allAnswersFlat->where('question_id', $q->id);
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'type' => $q->type,
                    'picture' => $q->slide,
                    'correct_answers' => $qAnswers->where('is_correct', true)->count(),
                    'avg_duration' => round($qAnswers->avg('time'), 1) ?? 0,
                    'options' => is_string($q->options) ? (json_decode($q->options, true) ?? []) : ($q->options ?? []),
                    'tutees_answers' => $allGameAttempts->map(function ($att) use ($q) {
                        $ans = collect($att->answers)->firstWhere('question_id', $q->id);
                        return $ans ? [
                            'id' => $att->student_id,
                            'name' => $att->student->name,
                            'image' => $att->student->avatar,
                            'selected_option' => [
                                'title' => $ans['answer'] ?? 'Not Answered',
                                'correct' => $ans['is_correct'] ?? false,
                                'score' => ($ans['is_correct'] ?? false) ? ($q->points ?? 1) : 0,
                                'answered_duration' => $ans['time'] ?? 0,
                            ]
                        ] : null;
                    })->filter()->values()
                ];
            });

        } catch (\Exception $e) {
            \Log::error('Report generation error: ' . $e->getMessage());
        }
    } else {
        // 3. Handle Non-Game (Solo) Exam Report
        $players = collect([[
            'id' => $student->id,
            'name' => $student->name,
            'image' => $student->avatar,
            'score' => $this->calculateScore($studentAnswers, $questions),
            'correct_answers' => collect($studentAnswers)->where('is_correct', true)->count(),
            'unanswered_questions' => $questions->count() - count($studentAnswers),
            'answered_questions' => $studentAnswers
        ]]);

        $questionsData = $questions->map(function ($q) use ($studentAnswers, $student) {
            $myAns = collect($studentAnswers)->firstWhere('question_id', $q->id);
            return [
                'id' => $q->id,
                'title' => $q->title,
                'options' => is_string($q->options) ? (json_decode($q->options, true) ?? []) : ($q->options ?? []),
                'tutees_answers' => $myAns ? [[
                    'id' => $student->id,
                    'name' => $student->name,
                    'selected_option' => [
                        'title' => $myAns['answer'],
                        'correct' => $myAns['is_correct'] ?? false,
                        'score' => ($myAns['is_correct'] ?? false) ? ($q->points ?? 1) : 0,
                    ]
                ]] : []
            ];
        });
    }

    // 4. Final Data Structure Construction
    return [
        'id' => $exam->id,
        'attempt_id' => $answer->id,
        'exam_id' => $exam->id,
        'game_id' => $answer->game_id,
        'is_assignment' => !empty($answer->game_id),
        'title' => $exam->title,
        'description' => $exam->description,
        'status' => $answer->status,
        'publish_type' => $exam->monetize ?? 'free',
        'created_at' => $exam->created_at?->toIso8601String(),
        'end_at' => $answer->completed_at?->toIso8601String(),
        'host' => $exam->institute->name ?? ($exam->teacher->name ?? 'System'),
        'duration' => $answer->total_duration_seconds,
        'time_spent' => $stats['total_time_spent'],
        'questions' => $questionsData,
        'players' => $players,
        'student_performance' => $stats['student_performance'],
        'score_breakdown' => $stats['score_breakdown'],
        'performance_analysis' => $stats['performance_analysis'],
        'difficult_questions' => $stats['difficult_questions'],
        'rank_info' => $rankInfo,
    ];
}


    private function calculateComprehensiveStats($studentAnswers, $questions)
    {
        $totalPointsEarned = 0;
        $totalPossiblePoints = 0;
        $questionsReport = [];
        $correctAnswerCount = 0;
        $totalAnsweredNonSlide = 0;
        $timeTakenSum = 0;
        $difficultyStats = [
            'easy' => ['total' => 0, 'correct' => 0],
            'medium' => ['total' => 0, 'correct' => 0],
            'hard' => ['total' => 0, 'correct' => 0]
        ];

        foreach ($questions as $question) {
            $studentAnswer = collect($studentAnswers)->firstWhere('question_id', $question->id);
            $isCorrect = $studentAnswer['is_correct'] ?? false;
            $timeSpent = $studentAnswer['time'] ?? 0;
            $difficulty = $question->difficulty_level ?? 'medium';
            $pointsEarned = 0;

            if ($question->type !== 'slide') {
                $questionPoints = $question->points ?? 1;
                $totalPossiblePoints += $questionPoints;
                $difficultyStats[$difficulty]['total']++;

                if ($studentAnswer) {
                    $totalAnsweredNonSlide++;
                    $timeTakenSum += $timeSpent;
                    if ($isCorrect) {
                        $correctAnswerCount++;
                        $pointsEarned = $questionPoints;
                        $difficultyStats[$difficulty]['correct']++;
                    }
                }
            }
            $totalPointsEarned += $pointsEarned;

            $questionsReport[] = [
                'id' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'difficulty' => $difficulty,
                'points' => $question->points ?? 1,
                'student_answer' => $studentAnswer['answer'] ?? 'Not Answered',
                'is_correct' => $isCorrect,
                'time_spent' => $timeSpent,
                'points_earned' => $pointsEarned,
            ];
        }

        $percentageScore = $totalPossiblePoints > 0 ?
            round(($totalPointsEarned / $totalPossiblePoints) * 100) : 0;

        return [
            'questions_report' => $questionsReport,
            'student_performance' => [
                'score' => $percentageScore,
                'correct_answers' => $correctAnswerCount,
                'total_questions' => $questions->where('type', '!=', 'slide')->count(),
                'answered_questions' => $totalAnsweredNonSlide,
                'points_earned' => $totalPointsEarned,
                'total_points' => $totalPossiblePoints,
            ],
            'score_breakdown' => [
                'percentage' => $percentageScore,
                'points' => $totalPointsEarned,
                'max_points' => $totalPossiblePoints,
                'accuracy' => $totalAnsweredNonSlide > 0 ?
                    round(($correctAnswerCount / $totalAnsweredNonSlide) * 100) : 0,
            ],
            'performance_analysis' => [
                'difficulty_breakdown' => $difficultyStats,
                'performance_level' => $this->getPerformanceLevel($percentageScore),
                'time_efficiency' => $this->getTimeEfficiency($timeTakenSum, $totalAnsweredNonSlide),
                'avg_time_per_question' => $totalAnsweredNonSlide > 0 ?
                    round($timeTakenSum / $totalAnsweredNonSlide) : 0,
            ],
            'total_time_spent' => $timeTakenSum,
            'difficult_questions' => collect($questionsReport)
                ->where('is_correct', false)
                ->where('type', '!=', 'slide')
                ->values()
                ->all(),
        ];
    }

    /**
     * Get performance level based on score
     */
    private function getPerformanceLevel($score)
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 80) return 'Good';
        if ($score >= 70) return 'Average';
        if ($score >= 60) return 'Below Average';
        return 'Needs Improvement';
    }

    /**
     * Get time efficiency rating
     */
    private function getTimeEfficiency($totalTime, $questionsAnswered)
    {
        if ($questionsAnswered == 0) return 'N/A';

        $avgTime = $totalTime / $questionsAnswered;

        if ($avgTime <= 30) return 'Fast';
        if ($avgTime <= 60) return 'Normal';
        if ($avgTime <= 120) return 'Slow';
        return 'Very Slow';
    }
}
