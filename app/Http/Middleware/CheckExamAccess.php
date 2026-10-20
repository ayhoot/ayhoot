<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Student;

class CheckExamAccess
{
    public function handle(Request $request, Closure $next)
    {
        $examId = $request->route('exam')?->id ?? $request->route('examId');
        
        if (!$examId) {
            return response(['message' => __('messages.exam_not_found')], 404);
        }

        $exam = Exam::find($examId);
        if (!$exam) {
            return response(['message' => __('messages.exam_not_found')], 404);
        }

        // If exam is free, allow access
        if ($exam->monetize == 'free') {
            return $next($request);
        }

        // Allow access when request originates from a live session join
        $livePin = $request->query('live_pin') ?? $request->header('X-LIVE-PIN');
        if ($livePin) {
            $liveSession = \App\Models\LiveSession::where('pin', $livePin)->first();
            if ($liveSession && $liveSession->exam_id == $exam->id) {
                // ensure authenticated student is a participant
                $student = auth('student')->user();
                if ($student && \App\Models\LiveSessionParticipant::where('live_session_id', $liveSession->id)->where('student_id', $student->id)->exists()) {
                    return $next($request);
                }
            }
        }

        // Check user authentication and access
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response(['message' => __('messages.unauthenticated')], 401);
        }



        $gameId = $request->route('game')?->id ?? $request->route('game')
            ?? $request->query('game') ?? $request->query('game_id')
            ?? $request->input('game_id');

        // Use the new access control method
        if ($exam->hasAccessFor($user, $gameId)) {
            return $next($request);
        }

        // For paid exams without access, return exam info with purchase requirement
        // instead of blocking completely
        $routeName = $request->route()->getName();
        $currentPath = $request->path();
        
        // Allow viewing exam details but block access to questions and gameplay
        if (str_contains($currentPath, '/questions') || 
            str_contains($currentPath, '/game') || 
            str_contains($currentPath, '/question') ||
            str_contains($currentPath, 'saveAnswer')) {
            return response([
                'message' => __('messages.exam_purchase_required'),
                'exam' => $exam,
                'needs_purchase' => true,
                'price' => $exam->price,
                'monetize' => $exam->monetize
            ], 402); // 402 Payment Required
        }

        // Allow viewing exam basic info (show, edit routes without restrictions)
        return $next($request);
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


}