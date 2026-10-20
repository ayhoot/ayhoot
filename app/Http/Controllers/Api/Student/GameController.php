<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Display a listing of games assigned to the authenticated student via groups.
     */
    public function index(Request $request)
    {
        $studentId = auth('student')->id();
        if (!$studentId) {
            return response()->json(['message' => __('messages.unauthenticated')], 401);
        }

        $query = Game::whereHas('groups.students', function ($query) use ($studentId) {
            $query->where('students.id', $studentId);
        })
        ->with([
            'exam.category',
            'exam.teacher',
            'exam.institute',
            'exam.questions',
            'teacher',
            'institute',
            'groups.students'
        ])
        ->withCount('groups');

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('exam', function ($eq) use ($search) {
                      $eq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $games = $query->latest('id')->paginate($request->get('per_page', 10));

        $gameIds = $games->pluck('id');
        $attempts = Answer::where('student_id', $studentId)
            ->whereIn('game_id', $gameIds)
            ->get()
            ->keyBy('game_id');

        $games->getCollection()->transform(function ($game) use ($attempts) {
            $attempt = $attempts->get($game->id);
            $game->student_attempt = $attempt ? [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'score' => $attempt->score ?? 0,
                'completed_at' => $attempt->completed_at,
            ] : null;
            $game->questions_count = $game->exam?->questions?->count() ?? 0;
            $game->students_count = $game->groups->flatMap->students->unique('id')->count();
            return $game;
        });

        return response()->json($games);
    }
}