<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Api\Instructor\Concerns\InteractsWithInstructor;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\Game;
use App\Models\Group;
use App\Models\GroupAssignment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    use InteractsWithInstructor;

    public function index(Request $request)
    {
        $instructor = $this->getInstructor();

        $hasLiveCol = \Illuminate\Support\Facades\Schema::hasTable('games') && \Illuminate\Support\Facades\Schema::hasColumn('games', 'live_session_id');

        // Auto-sync any existing LiveSessions for this instructor into the games table
        if ($hasLiveCol) {
            try {
                $liveSessions = \App\Models\LiveSession::where('host_id', $instructor->id)
                    ->where(function($q) use ($instructor) {
                        $q->where('host_type', get_class($instructor))
                          ->orWhere('host_type', class_basename($instructor))
                          ->orWhereNull('host_type');
                    })
                    ->with('exam')
                    ->get();

                foreach ($liveSessions as $ls) {
                    $ownerData = $this->isInstitute()
                        ? ['institute_id' => $instructor->id]
                        : ['teacher_id' => $instructor->id];

                    $desiredStatus = $ls->status === 'finished' ? 'completed' : 'active';
                    $game = \App\Models\Game::where('live_session_id', $ls->id)->first();

                    if (!$game) {
                        try {
                            $game = \App\Models\Game::create(array_merge($ownerData, [
                                'live_session_id' => $ls->id,
                                'exam_id' => $ls->exam_id,
                                'title' => $ls->title ?: ("Live Host: " . (optional($ls->exam)->title ?? "PIN {$ls->pin}")),
                                'status' => $desiredStatus,
                                'created_at' => $ls->created_at ?? now(),
                                'updated_at' => now(),
                            ]));
                        } catch (\Throwable $e) {
                            $game = \App\Models\Game::create(array_merge($ownerData, [
                                'live_session_id' => $ls->id,
                                'exam_id' => $ls->exam_id,
                                'title' => $ls->title ?: ("Live Host: " . (optional($ls->exam)->title ?? "PIN {$ls->pin}")),
                                'status' => 'active',
                                'created_at' => $ls->created_at ?? now(),
                                'updated_at' => now(),
                            ]));
                        }
                    } elseif ($ls->status === 'finished' && $game->status !== 'completed') {
                        try {
                            $game->update(['status' => 'completed']);
                        } catch (\Throwable $e) {
                            try {
                                $game->update(['status' => 'inactive']);
                            } catch (\Throwable $ignored) {}
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[GAME-INDEX] Live session sync error: ' . $e->getMessage());
            }
        }

        $query = Game::query()
            ->where($this->ownerColumn(), $instructor->id)
            ->with(['exam', 'groups']);

        if ($this->isInstitute()) {
            // Institute games are scoped solely by institute ownership.
        } else {
            // Teacher games: include games matching selected institute OR any live session games
            if ($instructor->selected_institute) {
                $query->where(function($q) use ($instructor, $hasLiveCol) {
                    $q->where('institute_id', $instructor->selected_institute);
                    if ($hasLiveCol) {
                        $q->orWhereNotNull('live_session_id');
                    }
                });
            } else {
                $query->where(function($q) use ($hasLiveCol) {
                    $q->whereNull('institute_id');
                    if ($hasLiveCol) {
                        $q->orWhereNotNull('live_session_id');
                    }
                });
            }
        }

        $games = $query->latest()->paginate(10);

        // Append counts and live metadata
        $games->getCollection()->transform(function ($game) {
            if (!empty($game->live_session_id)) {
                $game->is_live = true;
                $game->students_count = \App\Models\LiveSessionParticipant::where('live_session_id', $game->live_session_id)->count();
                $game->questions_count = $game->exam?->questions?->count() ?? 0;
                if ($game->status === 'inactive' || optional($game->liveSession)->status === 'finished') {
                    $game->status = 'completed';
                }
            } else {
                $game->is_live = false;
                $game->questions_count = $game->exam?->questions?->count() ?? 0;
                $game->students_count = \App\Models\Answer::where('game_id', $game->id)->distinct('student_id')->count('student_id');
            }
            return $game;
        });

        return response(['games' => $games]);
    }

    public function edit(Game $game)
    {
        if ($game->{$this->ownerColumn()} != $this->getInstructor()->id) {
            abort(403, __('messages.unauthorized_game_access'));
        }
        return response(['game' => $game->load('groups')]);
    }

    public function store(Request $request)
    {
        $instructor = $this->getInstructor();

        if ($this->isInstitute()) {
            $assignedGroupIds = GroupAssignment::where('institute_id', $instructor->id)->pluck('group_id');

            $data = $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'title' => 'nullable|string|max:255',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date|after_or_equal:start_at',
                'question_time' => 'nullable|in:on,off',
                'randomize_answer' => 'nullable|in:on,off',
                'group_ids' => 'nullable|array',
                'group_ids.*' => [
                    'integer',
                    Rule::exists('groups', 'id')->where(function ($query) use ($instructor, $assignedGroupIds) {
                        $query->where('institute_id', $instructor->id)
                              ->orWhereIn('id', $assignedGroupIds);
                    }),
                ],
            ]);
        } else {
            $data = $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'title' => 'nullable|string|max:255',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date|after_or_equal:start_at',
                'question_time' => 'nullable|in:on,off',
                'randomize_answer' => 'nullable|in:on,off',
                'group_ids' => 'nullable|array',
                'group_ids.*' => 'integer|exists:groups,id',
            ]);
        }

        // Verify exam belongs to this instructor.
        $exam = Exam::findOrFail($data['exam_id']);
        if ($exam->{$this->ownerColumn()} != $instructor->id) {
            return response([
                'message' => __('messages.unauthorized_exam_access'),
                'success' => false
            ], 403);
        }

        if ($this->isInstitute()) {
            $data['institute_id'] = $instructor->id;
        } else {
            // Teacher: verify groups belong to or are assigned to this teacher if provided.
            if (!empty($data['group_ids'])) {
                $assignedGroupIds = GroupAssignment::where('teacher_id', $instructor->id)->pluck('group_id');

                $teacherGroups = Group::query()
                    ->whereIn('id', $data['group_ids'])
                    ->where(function ($query) use ($instructor, $assignedGroupIds) {
                        $query->where('teacher_id', $instructor->id)
                              ->orWhereIn('id', $assignedGroupIds);
                    })
                    ->pluck('id')
                    ->toArray();

                if (count($teacherGroups) !== count($data['group_ids'])) {
                    return response([
                        'message' => __('messages.unauthorized_group_access'),
                        'success' => false
                    ], 403);
                }
            }

            $data['teacher_id'] = $instructor->id;
            $data['institute_id'] = $instructor->selected_institute;
            if (!$data['institute_id'] && !empty($data['group_ids'])) {
                $data['institute_id'] = Group::whereIn('id', $data['group_ids'])->whereNotNull('institute_id')->value('institute_id')
                    ?? GroupAssignment::whereIn('group_id', $data['group_ids'])->where('teacher_id', $instructor->id)->value('institute_id');
            }
        }

        // Idempotency check: prevent duplicate assignment creation within 5 seconds
        $recentDuplicateQuery = Game::where($this->ownerColumn(), $instructor->id)
            ->where('exam_id', $data['exam_id'])
            ->where('created_at', '>=', now()->subSeconds(5));

        if (!empty($data['end_at'])) {
            $recentDuplicateQuery->where('end_at', $data['end_at']);
        }
        $existingGame = $recentDuplicateQuery->latest('id')->first();

        if ($existingGame) {
            return response([
                'message' => __('messages.game_created_successfully'),
                'id' => $existingGame->id,
                'game_id' => $existingGame->id,
                'game' => $existingGame->load('groups'),
                'notifications_sent' => true,
                'success' => true
            ], 200);
        }

        $game = Game::create($data);
        $game->load(['exam', 'groups']);

        if (!empty($data['group_ids'])) {
            $game->groups()->attach($data['group_ids']);

            // Send notifications
            $notificationService = new NotificationService();
            $studentsNotified = $notificationService->notifyGameAssignment($game, $data['group_ids']);
            $notificationService->notifyExamCreatorOfAssignment($game);

            Log::info('Game assignment notifications sent', [
                'game_id' => $game->id,
                'students_notified' => $studentsNotified
            ]);
        }

        return response([
            'message' => __('messages.game_created_successfully'),
            'id' => $game->id,
            'game_id' => $game->id,
            'game' => $game->load('groups'),
            'notifications_sent' => !empty($data['group_ids']),
            'success' => true
        ], 201);
    }

    public function update(Request $request, Game $game)
    {
        if ($game->{$this->ownerColumn()} != $this->getInstructor()->id) {
            abort(403, __('messages.unauthorized_game_access'));
        }

        $instructor = $this->getInstructor();

        if ($this->isInstitute()) {
            $assignedGroupIds = GroupAssignment::where('institute_id', $instructor->id)->pluck('group_id');

            $data = $request->validate([
                'exam_id' => 'sometimes|required|exists:exams,id',
                'title' => 'sometimes|required|string|max:255',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date|after_or_equal:start_at',
                'question_time' => 'nullable|in:on,off',
                'randomize_answer' => 'nullable|in:on,off',
                'group_ids' => 'nullable|array',
                'group_ids.*' => [
                    'integer',
                    Rule::exists('groups', 'id')->where(function ($query) use ($instructor, $assignedGroupIds) {
                        $query->where('institute_id', $instructor->id)
                              ->orWhereIn('id', $assignedGroupIds);
                    }),
                ],
            ]);
        } else {
            $data = $request->validate([
                'exam_id' => 'sometimes|required|exists:exams,id',
                'title' => 'sometimes|required|string|max:255',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|date|after_or_equal:start_at',
                'question_time' => 'nullable|in:on,off',
                'randomize_answer' => 'nullable|in:on,off',
                'group_ids' => 'nullable|array',
                'group_ids.*' => 'integer|exists:groups,id',
            ]);
        }

        // Verify exam belongs to this instructor if exam_id is being updated.
        if (isset($data['exam_id'])) {
            $exam = Exam::findOrFail($data['exam_id']);
            if ($exam->{$this->ownerColumn()} != $instructor->id) {
                return response([
                    'message' => __('messages.unauthorized_exam_access'),
                    'success' => false
                ], 403);
            }
        }

        if (!$this->isInstitute()) {
            // Teacher: verify groups belong to or are assigned to this teacher if provided.
            if (!empty($data['group_ids'])) {
                $assignedGroupIds = GroupAssignment::where('teacher_id', $instructor->id)->pluck('group_id');

                $teacherGroups = Group::query()
                    ->whereIn('id', $data['group_ids'])
                    ->where(function ($query) use ($instructor, $assignedGroupIds) {
                        $query->where('teacher_id', $instructor->id)
                              ->orWhereIn('id', $assignedGroupIds);
                    })
                    ->pluck('id')
                    ->toArray();

                if (count($teacherGroups) !== count($data['group_ids'])) {
                    return response([
                        'message' => __('messages.unauthorized_group_access'),
                        'success' => false
                    ], 403);
                }
            }

            if (!$game->institute_id && !empty($data['group_ids'])) {
                $data['institute_id'] = Group::whereIn('id', $data['group_ids'])->whereNotNull('institute_id')->value('institute_id')
                    ?? GroupAssignment::whereIn('group_id', $data['group_ids'])->where('teacher_id', $instructor->id)->value('institute_id');
            }
        }

        $game->update($data);

        // If group_ids are provided, sync them. This will add/remove groups as needed.
        if ($request->has('group_ids')) {
            $game->groups()->sync($data['group_ids'] ?? []);
        }

        return response([
            'message' => __('messages.game_updated_successfully'),
            'game' => $game->fresh()->load('groups'),
            'success' => true
        ]);
    }

    public function delete(Game $game)
    {
        if ($game->{$this->ownerColumn()} != $this->getInstructor()->id) {
            abort(403, __('messages.unauthorized_game_access'));
        }
        $game->delete();
        return response(['message' => __('messages.game_deleted_successfully')]);
    }

    public function getReport(Game $game)
    {
        if ($game->{$this->ownerColumn()} != $this->getInstructor()->id) {
            abort(403, __('messages.unauthorized_game_access'));
        }

        $game->load(['institute', 'teacher', 'exam.questions', 'groups.students']);
        if (!$game->exam) {
            return response(['message' => __('messages.game_not_associated_with_exam')], 409);
        }

        $exam = $game->exam;
        $questions = $exam->questions;
        $gameGroups = $game->groups;

        // 1. Gather all unique students who are in the assigned groups
        $assignedStudents = $gameGroups->flatMap(fn($g) => $g->students)->keyBy('id');
        $assignedStudentIds = $assignedStudents->keys()->toArray();

        // 2. Fetch all attempts for this game
        $allAttempts = Answer::where('game_id', $game->id)
            ->with('student')
            ->get();

        foreach ($allAttempts as $attempt) {
            if ($attempt->student && !$assignedStudents->has($attempt->student_id)) {
                $assignedStudents->put($attempt->student_id, $attempt->student);
            }
        }

        $attemptsByStudentId = $allAttempts->keyBy('student_id');

        $scorableQuestions = $questions->where('type', '!=', 'slide');
        $scorableQuestionsCount = $scorableQuestions->count();
        $totalQuestionsCount = $questions->count();

        // 3. Build player data for each student
        $playersData = $assignedStudents->values()->map(function ($student) use ($questions, $scorableQuestionsCount, $totalQuestionsCount, $gameGroups, $attemptsByStudentId) {
            $attempt = $attemptsByStudentId->get($student->id);
            $studentAnswers = collect($attempt?->answers ?? []);

            $correctCount = 0;
            $totalScore = 0;
            $answeredQuestionCount = 0;

            if ($attempt) {
                foreach ($studentAnswers as $studentAnswer) {
                    if ($studentAnswer['is_correct'] ?? false) {
                        $correctCount++;
                    }
                    $question = $questions->firstWhere('id', $studentAnswer['question_id']);
                    if ($question && $question->type !== 'slide') {
                        $totalScore += ($studentAnswer['is_correct'] ?? false) ? ($question->points ?? 1000) : 0;
                    }
                    $answeredQuestionCount++;
                }
            }

            $studentGroups = $gameGroups->filter(function($group) use ($student) {
                return $group->students->contains('id', $student->id);
            });

            $unansweredCount = max(0, $totalQuestionsCount - $answeredQuestionCount);
            $isCompleted = ($attempt && $attempt->status === 'completed');
            $status = $attempt ? $attempt->status : 'not_started';

            return [
                'id' => $student->id,
                'name' => $student->name,
                'image' => $student->avatar,
                'status' => $status,
                'is_completed' => $isCompleted,
                'correct_answers' => $correctCount,
                'unanswered_questions' => $unansweredCount,
                'score' => $totalScore,
                'total_score' => $totalScore,
                'answered_questions' => $studentAnswers,
                'completed_at' => $attempt?->completed_at?->timestamp ?? 0,
                'group_ids' => $studentGroups->pluck('id')->toArray(),
                'groups' => $studentGroups->map(fn($g) => [
                    'id' => $g->id, 
                    'name' => $g->title ?? $g->name
                ])->values()
            ];
        });

        // 4. Rank players: Completed first by score DESC, then uncompleted
        $rankedPlayers = $playersData->sort(function ($a, $b) {
            if ($a['is_completed'] !== $b['is_completed']) {
                return $b['is_completed'] ? 1 : -1;
            }
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }
            return $a['completed_at'] <=> $b['completed_at'];
        })->values()->map(function ($player, $index) {
            $player['rank'] = $index + 1;
            return $player;
        });

        // 5. Build Group data including all students in each group
        $groupsData = $gameGroups->map(function ($group) use ($rankedPlayers) {
            $groupPlayers = $rankedPlayers->filter(fn($p) => in_array($group->id, $p['group_ids']))
                ->sort(function ($a, $b) {
                    if ($a['is_completed'] !== $b['is_completed']) {
                        return $b['is_completed'] ? 1 : -1;
                    }
                    return $b['score'] <=> $a['score'];
                })
                ->values()
                ->map(function($p, $index) {
                    $p['group_rank'] = $index + 1;
                    return $p;
                });

            $completedGroupPlayers = $groupPlayers->filter(fn($p) => $p['is_completed']);
            $avgScore = $completedGroupPlayers->isNotEmpty() 
                ? round($completedGroupPlayers->avg('score'), 1) 
                : 0;

            return [
                'id' => $group->id,
                'name' => $group->title ?? $group->name,
                'players_count' => $groupPlayers->count(),
                'completed_count' => $completedGroupPlayers->count(),
                'avg_score' => $avgScore,
                'players' => $groupPlayers
            ];
        });

        // 6. Attach group_ranks to each player
        $finalRankedPlayers = $rankedPlayers->map(function($player) use ($groupsData) {
            $ranksInGroups = [];
            foreach ($player['group_ids'] as $groupId) {
                $groupEntry = $groupsData->firstWhere('id', $groupId);
                if ($groupEntry) {
                    $pInG = collect($groupEntry['players'])->firstWhere('id', $player['id']);
                    $ranksInGroups[] = [
                        'group_id' => $groupId,
                        'group_name' => $groupEntry['name'],
                        'rank' => $pInG['group_rank'] ?? null
                    ];
                }
            }
            $player['group_ranks'] = $ranksInGroups;
            return $player;
        });

        // 7. Question-by-question analytics
        $completedAttempts = $allAttempts->where('status', 'completed');
        $totalCompletedCount = $completedAttempts->count();
        $allIndividualAnswers = $allAttempts->pluck('answers')->flatten(1);

        $questionsData = $questions->map(function ($question) use ($allIndividualAnswers, $allAttempts) {
            $answersForThisQuestion = $allIndividualAnswers->where('question_id', $question->id);
            $correctCount = $answersForThisQuestion->where('is_correct', true)->count();
            
            $tuteeAnswersList = $allAttempts->map(function ($attempt) use ($question) {
                if (!$attempt->student) return null;
                $answer = collect($attempt->answers)->firstWhere('question_id', $question->id);
                if (!$answer) return null;
                return [
                    'id' => $attempt->student_id,
                    'name' => $attempt->student->name,
                    'selected_option' => [
                        'title' => $answer['answer'] ?? 'Not Answered',
                        'correct' => $answer['is_correct'] ?? false,
                        'score' => ($answer['is_correct'] ?? false) ? ($question->points ?? 1000) : 0,
                        'answered_duration' => $answer['time'] ?? 0,
                    ]
                ];
            })->filter()->values();

            return [
                'id' => $question->id,
                'title' => $question->title,
                'type' => $question->type,
                'picture' => $question->slide,
                'order' => $question->id,
                'correct_answers' => $correctCount,
                'avg_duration' => round($answersForThisQuestion->avg('time') ?? 0, 1),
                'options' => is_string($question->options) ? (json_decode($question->options, true) ?? []) : ($question->options ?? []),
                'tutees_answers' => $tuteeAnswersList,
            ];
        });

        $avgScorePercent = ($totalCompletedCount > 0 && $scorableQuestionsCount > 0)
            ? round(($finalRankedPlayers->where('is_completed', true)->sum('correct_answers') / ($scorableQuestionsCount * $totalCompletedCount)) * 100, 1)
            : 0;

        $report = [
            'id' => $game->id,
            'title' => $game->title,
            'end_at' => $game->end_at,
            'exam_id' => $game->exam_id,
            'institute' => $game->institute,
            'host' => $game->institute->name ?? optional($game->teacher)->name,
            'question_count' => $questions->count(),
            'duration' => $questions->sum('duration'),
            'avg_score' => $avgScorePercent,
            'players' => $finalRankedPlayers,
            'groups' => $groupsData,
            'questions' => $questionsData,
            'difficult_questions' => $questionsData->filter(fn($q) => $q['type'] !== 'slide' && $totalCompletedCount > 0 && ($q['correct_answers'] / $totalCompletedCount) < 0.35)->values(),
            'help_needers' => $finalRankedPlayers->filter(fn($p) => $p['is_completed'] && $scorableQuestionsCount > 0 && ($p['correct_answers'] / $scorableQuestionsCount) < 0.35)->values(),
            'not_finished' => $finalRankedPlayers->filter(fn($p) => !$p['is_completed'] || $p['unanswered_questions'] > 0)->values(),
        ];

        return response($report);
    }
}
