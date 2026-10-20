<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Answer;
use App\Models\Exam;
use App\Models\Course;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Get dashboard reports for the authenticated user
     */
    public function getDashboardReports(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        $timeframe = $request->get('timeframe', '30'); // days
        $startDate = Carbon::now()->subDays($timeframe);
        
        switch ($userType) {
            case 'teacher':
                return $this->getTeacherReports($user, $startDate);
            case 'institute':
                return $this->getInstituteReports($user, $startDate);
            case 'student':
                return $this->getStudentReports($user, $startDate);
            default:
                return response(['message' => 'Invalid user type'], 400);
        }
    }

    /**
     * Get detailed reports list
     */
    public function getReportsList(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        $query = Game::with(['exam', 'institute', 'teacher'])
            ->withCount(['answers as completed_answers' => function ($q) {
                $q->where('status', 'completed');
            }]);
            
        // Filter based on user type
        if ($userType == 'teacher') {
            $query->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)
                  ->orWhereHas('exam', function ($examQuery) use ($user) {
                      $examQuery->where('teacher_id', $user->id);
                  });
            });
        } elseif ($userType == 'institute') {
            $query->where(function ($q) use ($user) {
                $q->where('institute_id', $user->id)
                  ->orWhereHas('exam', function ($examQuery) use ($user) {
                      $examQuery->where('institute_id', $user->id);
                  });
            });
        } else {
            // Students can only see games they participated in
            $query->whereHas('answers', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            });
        }
        
        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($game) use ($userType) {
                return [
                    'id' => $game->id,
                    'title' => $game->title ?? $game->exam->title,
                    'exam_title' => $game->exam->title,
                    'exam_id' => $game->exam_id,
                    'creator' => $game->teacher ?? $game->institute,
                    'creator_type' => $game->teacher ? 'teacher' : 'institute',
                    'participants_count' => $game->completed_answers,
                    'created_at' => $game->created_at,
                    'status' => $game->end_at && Carbon::now()->gt($game->end_at) ? 'completed' : 'active',
                    'can_view_report' => $this->canViewReport($game, $userType)
                ];
            });
            
        return response(['reports' => $reports]);
    }

    private function getTeacherReports($teacher, $startDate)
    {
        // Games created by teacher
        $createdGames = Game::where('teacher_id', $teacher->id)
            ->where('created_at', '>=', $startDate)
            ->withCount(['answers as participants' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->get();

        // Games using teacher's exams
        $examUsageGames = Game::whereHas('exam', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->where(fn($q) => $q->whereNull('teacher_id')->orWhere('teacher_id', '!=', $teacher->id)) // Exclude own games
        ->where('created_at', '>=', $startDate)
        ->with(['institute', 'teacher'])
        ->withCount(['answers as participants' => function ($q) {
            $q->where('status', 'completed');
        }])
        ->get();

        // Content statistics
        $totalExams = Exam::where('teacher_id', $teacher->id)->count();
        $totalCourses = Course::where('teacher_id', $teacher->id)->count();
        $totalPlays = Game::whereHas('exam', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->sum('plays_count') ?? 0;

        // Recent activity
        $recentActivity = collect()
            ->merge($createdGames->map(function ($game) {
                return [
                    'type' => 'game_created',
                    'title' => "Created assignment: {$game->exam->title}",
                    'participants' => $game->participants,
                    'date' => $game->created_at
                ];
            }))
            ->merge($examUsageGames->map(function ($game) {
                $creator = $game->teacher ?? $game->institute;
                return [
                    'type' => 'exam_assigned',
                    'title' => "Your exam '{$game->exam->title}' was assigned by {$creator->name}",
                    'participants' => $game->participants,
                    'date' => $game->created_at
                ];
            }))
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return response([
            'summary' => [
                'total_exams' => $totalExams,
                'total_courses' => $totalCourses,
                'total_assignments' => $createdGames->count(),
                'total_plays' => $totalPlays,
                'exam_usage_by_others' => $examUsageGames->count()
            ],
            'created_games' => $createdGames->map(function ($game) {
                return [
                    'id' => $game->id,
                    'title' => $game->title ?? $game->exam->title,
                    'exam_title' => $game->exam->title,
                    'participants' => $game->participants,
                    'created_at' => $game->created_at
                ];
            }),
            'exam_usage' => $examUsageGames->map(function ($game) {
                return [
                    'id' => $game->id,
                    'exam_title' => $game->exam->title,
                    'assigned_by' => $game->teacher ?? $game->institute,
                    'assigner_type' => $game->teacher ? 'teacher' : 'institute',
                    'participants' => $game->participants,
                    'created_at' => $game->created_at
                ];
            }),
            'recent_activity' => $recentActivity
        ]);
    }

    private function getInstituteReports($institute, $startDate)
    {
        // Games created by institute
        $createdGames = Game::where('institute_id', $institute->id)
            ->where('created_at', '>=', $startDate)
            ->with(['exam.teacher'])
            ->withCount(['answers as participants' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->get();

        // Games created by institute's teachers
        $teacherGames = Game::whereHas('teacher', function ($q) use ($institute) {
            $q->where('selected_institute', $institute->id);
        })
        ->where('created_at', '>=', $startDate)
        ->with(['teacher', 'exam'])
        ->withCount(['answers as participants' => function ($q) {
            $q->where('status', 'completed');
        }])
        ->get();

        // Content statistics
        $totalExams = Exam::where('institute_id', $institute->id)->count();
        $totalCourses = Course::where('institute_id', $institute->id)->count();
        $totalTeachers = $institute->teachers()->count();

        // Recent activity
        $recentActivity = collect()
            ->merge($createdGames->map(function ($game) {
                return [
                    'type' => 'game_created',
                    'title' => "Created assignment: {$game->exam->title}",
                    'participants' => $game->participants,
                    'date' => $game->created_at
                ];
            }))
            ->merge($teacherGames->map(function ($game) {
                return [
                    'type' => 'teacher_assignment',
                    'title' => "Teacher {$game->teacher->name} assigned: {$game->exam->title}",
                    'participants' => $game->participants,
                    'date' => $game->created_at
                ];
            }))
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return response([
            'summary' => [
                'total_exams' => $totalExams,
                'total_courses' => $totalCourses,
                'total_teachers' => $totalTeachers,
                'institute_assignments' => $createdGames->count(),
                'teacher_assignments' => $teacherGames->count()
            ],
            'institute_games' => $createdGames->map(function ($game) {
                return [
                    'id' => $game->id,
                    'title' => $game->title ?? $game->exam->title,
                    'exam_title' => $game->exam->title,
                    'exam_creator' => $game->exam->teacher,
                    'participants' => $game->participants,
                    'created_at' => $game->created_at
                ];
            }),
            'teacher_games' => $teacherGames->map(function ($game) {
                return [
                    'id' => $game->id,
                    'title' => $game->title ?? $game->exam->title,
                    'exam_title' => $game->exam->title,
                    'teacher' => $game->teacher,
                    'participants' => $game->participants,
                    'created_at' => $game->created_at
                ];
            }),
            'recent_activity' => $recentActivity
        ]);
    }

    private function getStudentReports($student, $startDate)
    {
        // Games student participated in
        $participatedGames = Answer::where('student_id', $student->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->with(['game.exam', 'game.teacher', 'game.institute'])
            ->get()
            ->map(function ($answer) {
                $game = $answer->game;
                $answers = collect($answer->answers ?? []);
                $correctCount = $answers->where('is_correct', true)->count();
                $totalQuestions = $game->exam->questions()->count();
                
                return [
                    'id' => $game->id,
                    'title' => $game->title ?? $game->exam->title,
                    'exam_title' => $game->exam->title,
                    'creator' => $game->teacher ?? $game->institute,
                    'creator_type' => $game->teacher ? 'teacher' : 'institute',
                    'score' => $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 1) : 0,
                    'correct_answers' => $correctCount,
                    'total_questions' => $totalQuestions,
                    'completed_at' => $answer->updated_at
                ];
            });

        // Self-played exams (multiple attempts)
        $selfPlayedExams = Answer::where('student_id', $student->id)
            ->whereNull('game_id') // Self-played exams don't have game_id
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->with(['exam'])
            ->get()
            ->groupBy('exam_id')
            ->map(function ($attempts, $examId) {
                $exam = $attempts->first()->exam;
                $bestAttempt = $attempts->sortByDesc(function ($attempt) {
                    $answers = collect($attempt->answers ?? []);
                    return $answers->where('is_correct', true)->count();
                })->first();
                
                $bestAnswers = collect($bestAttempt->answers ?? []);
                $correctCount = $bestAnswers->where('is_correct', true)->count();
                $totalQuestions = $exam->questions()->count();
                
                return [
                    'exam_id' => $examId,
                    'exam_title' => $exam->title,
                    'attempts_count' => $attempts->count(),
                    'best_score' => $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 1) : 0,
                    'best_correct_answers' => $correctCount,
                    'total_questions' => $totalQuestions,
                    'last_attempt' => $attempts->max('created_at')
                ];
            })
            ->values();

        return response([
            'summary' => [
                'total_assignments' => $participatedGames->count(),
                'total_self_played' => $selfPlayedExams->count(),
                'average_score' => $participatedGames->avg('score') ?? 0
            ],
            'participated_games' => $participatedGames,
            'self_played_exams' => $selfPlayedExams
        ]);
    }

    private function canViewReport($game, $userType)
    {
        $user = $this->getAuthenticatedUser();
        
        if ($userType == 'teacher') {
            return $game->teacher_id == $user->id || $game->exam->teacher_id == $user->id;
        } elseif ($userType == 'institute') {
            return $game->institute_id == $user->id || $game->exam->institute_id == $user->id;
        } elseif ($userType == 'student') {
            return Answer::where('game_id', $game->id)
                ->where('student_id', $user->id)
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