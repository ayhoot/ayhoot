<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Game;
use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institute;

class NotificationService
{
    /**
     * Send notification when a game is assigned to groups
     */
    public function notifyGameAssignment(Game $game, array $groupIds)
    {
        $creator = $game->teacher ?? $game->institute;
        $creatorType = $game->teacher ? 'teacher' : 'institute';
        $creatorName = $creator ? $creator->name : '';

        // Get all students in the assigned groups
        $students = Student::whereHas('groups', function ($query) use ($groupIds) {
            $query->whereIn('groups.id', $groupIds);
        })->get();

        // Notify all students in the groups
        foreach ($students as $student) {
            $this->createNotification([
                'user_id' => $student->id,
                'user_type' => 'student',
                'type' => 'game_assigned',
                'title' => 'New Assignment',
                'message' => "You have been assigned a new exam: {$game->exam->title} by {$creatorName}",
                'data' => [
                    'game_id' => $game->id,
                    'exam_id' => $game->exam_id,
                    'creator_type' => $creatorType,
                    'creator_id' => $creator->id,
                    'creator_name' => $creatorName
                ]
            ]);
        }

        // Notify the creator about successful assignment
        $this->createNotification([
            'user_id' => $creator->id,
            'user_type' => $creatorType,
            'type' => 'game_assignment_created',
            'title' => 'Assignment Created',
            'message' => "Your assignment '{$game->exam->title}' has been sent to " . count($students) . " students",
            'data' => [
                'game_id' => $game->id,
                'exam_id' => $game->exam_id,
                'student_count' => count($students)
            ]
        ]);

        return count($students);
    }

    /**
     * Notify exam creator when their exam is assigned by others
     */
    public function notifyExamCreatorOfAssignment(Game $game)
    {
        $exam = $game->exam;
        $assigner = $game->teacher ?? $game->institute;
        $assignerType = $game->teacher ? 'teacher' : 'institute';
        $assignerName = $assigner ? $assigner->name : '';

        // If the exam creator is different from the assigner, notify them
        if ($exam->teacher_id && $exam->teacher_id !== $assigner->id) {
            $this->createNotification([
                'user_id' => $exam->teacher_id,
                'user_type' => 'teacher',
                'type' => 'exam_assigned_by_others',
                'title' => 'Your Exam Was Assigned',
                'message' => "Your exam '{$exam->title}' has been assigned by {$assignerName}",
                'data' => [
                    'game_id' => $game->id,
                    'exam_id' => $exam->id,
                    'assigner_type' => $assignerType,
                    'assigner_id' => $assigner->id,
                    'assigner_name' => $assignerName
                ]
            ]);
        }

        // If the exam belongs to an institute and was assigned by a teacher, notify the institute
        if ($exam->institute_id && $assignerType == 'teacher' && $exam->institute_id !== $assigner->id) {
            $this->createNotification([
                'user_id' => $exam->institute_id,
                'user_type' => 'institute',
                'type' => 'exam_assigned_by_teacher',
                'title' => 'Your Exam Was Assigned',
                'message' => "Your exam '{$exam->title}' has been assigned by teacher {$assignerName}",
                'data' => [
                    'game_id' => $game->id,
                    'exam_id' => $exam->id,
                    'assigner_type' => $assignerType,
                    'assigner_id' => $assigner->id,
                    'assigner_name' => $assignerName
                ]
            ]);
        }
    }

    /**
     * Notify about purchase completion
     */
    public function notifyPurchaseCompletion($purchaser, $content, $contentType, $amount)
    {
        $purchaserType = strtolower(class_basename($purchaser));

        $this->createNotification([
            'user_id' => $purchaser->id,
            'user_type' => $purchaserType,
            'type' => 'purchase_completed',
            'title' => 'Purchase Successful',
            'message' => "You have successfully purchased {$contentType}: {$content->title}",
            'data' => [
                'content_type' => $contentType,
                'content_id' => $content->id,
                'content_title' => $content->title,
                'amount_paid' => $amount
            ]
        ]);

        // Notify content creator about the purchase
        if ($content->teacher_id) {
            $this->createNotification([
                'user_id' => $content->teacher_id,
                'user_type' => 'teacher',
                'type' => 'content_purchased',
                'title' => 'Content Purchased',
                'message' => "Your {$contentType} '{$content->title}' has been purchased by {$purchaser->name}",
                'data' => [
                    'content_type' => $contentType,
                    'content_id' => $content->id,
                    'purchaser_type' => $purchaserType,
                    'purchaser_id' => $purchaser->id,
                    'purchaser_name' => $purchaser->name,
                    'amount_paid' => $amount
                ]
            ]);
        }
    }

    /**
     * Notify about game report generation
     */
    public function notifyGameReportGenerated(Game $game, $reportData)
    {
        $creator = $game->institute ?? $game->teacher;
        $creatorType = $game->institute ? 'institute' : 'teacher';

        $this->createNotification([
            'user_id' => $creator->id,
            'user_type' => $creatorType,
            'type' => 'game_report_ready',
            'title' => 'Game Report Ready',
            'message' => "Report for '{$game->exam->title}' is ready with {$reportData['players']->count()} participants",
            'data' => [
                'game_id' => $game->id,
                'exam_id' => $game->exam_id,
                'participant_count' => $reportData['players']->count(),
                'avg_score' => $reportData['avg_score']
            ]
        ]);
    }

    /**
     * Create a notification record
     */
    private function createNotification(array $data)
    {
        // Map user_type to model class name
        $modelMap = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class,
        ];

        $notifiableType = $modelMap[$data['user_type']] ?? $data['user_type'];

        return Notification::create([
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $data['user_id'],
            'type' => $data['type'],
            'message' => $data['message'] ?? '',
            'data' => $data['data'] ?? [],
            'read_at' => null,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId, $userType)
    {
        $modelMap = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class,
        ];

        $notifiableType = $modelMap[$userType] ?? $userType;

        return Notification::where('id', $notificationId)
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', $notifiableType)
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications($userId, $userType, $limit = 10)
    {
        $modelMap = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class,
        ];

        $notifiableType = $modelMap[$userType] ?? $userType;

        return Notification::where('notifiable_id', $userId)
            ->where('notifiable_type', $notifiableType)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user
     */
    public function getAllNotifications($userId, $userType, $perPage = 15)
    {
        $modelMap = [
            'student' => Student::class,
            'teacher' => Teacher::class,
            'institute' => Institute::class,
        ];

        $notifiableType = $modelMap[$userType] ?? $userType;

        return Notification::where('notifiable_id', $userId)
            ->where('notifiable_type', $notifiableType)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
