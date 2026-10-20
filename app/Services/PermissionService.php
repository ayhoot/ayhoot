<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Institute;

class PermissionService
{
    /**
     * Check if user can create exams based on their plan
     */
    public function canCreateExam($user): array
    {
        if (!$user || !method_exists($user, 'getFeatureValue')) {
            return ['allowed' => false, 'reason' => 'User not found or invalid'];
        }

        $maxExams = $user->getFeatureValue('ayhoots');
        
        if (!$maxExams || $maxExams == 0) {
            return ['allowed' => false, 'reason' => 'Exam creation not enabled in your plan'];
        }

        // Count soft-deleted exams too, so deleting one doesn't free a slot and let a
        // free-plan user bypass their limit by delete-and-recreate.
        $currentExams = $user->exams()->withTrashed()->where('archive', null)->count();

        if ($currentExams >= $maxExams) {
            return [
                'allowed' => false, 
                'reason' => "You have reached the maximum limit of {$maxExams} exams for your plan",
                'current' => $currentExams,
                'limit' => $maxExams
            ];
        }

        return [
            'allowed' => true, 
            'current' => $currentExams,
            'limit' => $maxExams,
            'remaining' => $maxExams - $currentExams
        ];
    }

    /**
     * Check if user can create courses based on their plan
     */
    public function canCreateCourse($user): array
    {
        if (!$user || !method_exists($user, 'getFeatureValue')) {
            return ['allowed' => false, 'reason' => 'User not found or invalid'];
        }

        $maxCourses = $user->getFeatureValue('courses');
        
        if (!$maxCourses || $maxCourses == 0) {
            return ['allowed' => false, 'reason' => 'Course creation not enabled in your plan'];
        }

        // Count soft-deleted courses too (prevent delete-and-recreate limit bypass).
        $currentCourses = $user->courses()->withTrashed()->count();

        if ($currentCourses >= $maxCourses) {
            return [
                'allowed' => false, 
                'reason' => "You have reached the maximum limit of {$maxCourses} courses for your plan",
                'current' => $currentCourses,
                'limit' => $maxCourses
            ];
        }

        return [
            'allowed' => true, 
            'current' => $currentCourses,
            'limit' => $maxCourses,
            'remaining' => $maxCourses - $currentCourses
        ];
    }

    /**
     * Check if user can create groups based on their plan
     */
    public function canCreateGroup($user): array
    {
        if (!$user || !method_exists($user, 'getFeatureValue')) {
            return ['allowed' => false, 'reason' => 'User not found or invalid'];
        }

        $maxGroups = $user->getFeatureValue('groups');
        
        if (!$maxGroups || $maxGroups == 0) {
            return ['allowed' => false, 'reason' => 'Group creation not enabled in your plan'];
        }

        // Get current groups count based on user type
        if ($user instanceof Teacher) {
            $currentGroups = $user->groupAssignments()->count();
        } elseif ($user instanceof Institute) {
            $currentGroups = $user->groupAssignments()->count();
        } else {
            return ['allowed' => false, 'reason' => 'Invalid user type'];
        }
        
        if ($currentGroups >= $maxGroups) {
            return [
                'allowed' => false, 
                'reason' => "You have reached the maximum limit of {$maxGroups} groups for your plan",
                'current' => $currentGroups,
                'limit' => $maxGroups
            ];
        }

        return [
            'allowed' => true, 
            'current' => $currentGroups,
            'limit' => $maxGroups,
            'remaining' => $maxGroups - $currentGroups
        ];
    }

    /**
     * Check participant limit for exams/courses
     */
    public function canAddParticipant($user, $currentParticipants): array
    {
        if (!$user || !method_exists($user, 'getFeatureValue')) {
            return ['allowed' => false, 'reason' => 'User not found or invalid'];
        }

        $maxParticipants = $user->getFeatureValue('participant_limit');
        
        if (!$maxParticipants || $maxParticipants == 0) {
            return ['allowed' => false, 'reason' => 'Participant limit not set in your plan'];
        }

        if ($currentParticipants >= $maxParticipants) {
            return [
                'allowed' => false, 
                'reason' => "You have reached the maximum participant limit of {$maxParticipants} for your plan",
                'current' => $currentParticipants,
                'limit' => $maxParticipants
            ];
        }

        return [
            'allowed' => true, 
            'current' => $currentParticipants,
            'limit' => $maxParticipants,
            'remaining' => $maxParticipants - $currentParticipants
        ];
    }

    /**
     * Check monthly tutees limit
     */
    public function canAddTutee($user): array
    {
        if (!$user || !method_exists($user, 'getFeatureValue')) {
            return ['allowed' => false, 'reason' => 'User not found or invalid'];
        }

        $maxTutees = $user->getFeatureValue('tutees_per_month');
        
        if (!$maxTutees || $maxTutees == 0) {
            return ['allowed' => false, 'reason' => 'Tutee limit not set in your plan'];
        }

        // Count current month's tutees
        $currentMonth = now()->format('Y-m');
        
        if ($user instanceof Teacher) {
            $currentTutees = $user->students()
                ->wherePivot('created_at', '>=', now()->startOfMonth())
                ->count();
        } elseif ($user instanceof Institute) {
            $currentTutees = $user->students()
                ->wherePivot('created_at', '>=', now()->startOfMonth())
                ->count();
        } else {
            return ['allowed' => false, 'reason' => 'Invalid user type'];
        }

        if ($currentTutees >= $maxTutees) {
            return [
                'allowed' => false, 
                'reason' => "You have reached the maximum tutee limit of {$maxTutees} for this month",
                'current' => $currentTutees,
                'limit' => $maxTutees
            ];
        }

        return [
            'allowed' => true, 
            'current' => $currentTutees,
            'limit' => $maxTutees,
            'remaining' => $maxTutees - $currentTutees
        ];
    }

    /**
     * Check if user can monetize content
     */
    public function canMonetize($user): array
    {
        if (!$user || !method_exists($user, 'getFeatureValue')) {
            return ['allowed' => false, 'reason' => 'User not found or invalid'];
        }

        $monetizeValue = $user->getFeatureValue('monetize');
        
        if (!$monetizeValue || $monetizeValue == 0) {
            return ['allowed' => false, 'reason' => 'Monetization not enabled in your plan'];
        }

        return ['allowed' => true];
    }

    /**
     * Get all permissions for a user
     */
    public function getAllPermissions($user): array
    {
        return [
            'exams' => $this->canCreateExam($user),
            'courses' => $this->canCreateCourse($user),
            'groups' => $this->canCreateGroup($user),
            'tutees' => $this->canAddTutee($user),
            'monetize' => $this->canMonetize($user),
            'plan' => [
                'name' => $user->plan?->title ?? 'No Plan',
                'is_free' => $user->plan?->is_free ?? true,
                'features' => $user->plan?->features->map(function($feature) {
                    return [
                        'name' => $feature->title,
                        'role' => $feature->role,
                        'value' => $feature->value
                    ];
                }) ?? []
            ]
        ];
    }
}