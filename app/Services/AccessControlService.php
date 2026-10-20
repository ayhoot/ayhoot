<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Institute;
use App\Models\Student;

class AccessControlService
{
    public function checkExamAccess($user, Exam $exam): array
    {
        $hasAccess = false;
        $needsPurchase = false;
        $isOwner = false;
        $isPurchased = false;

        // Check ownership
        if ($user instanceof Teacher && $exam->teacher_id == $user->id) {
            $hasAccess = true;
            $isOwner = true;
        } elseif ($user instanceof Institute && $exam->institute_id == $user->id) {
            $hasAccess = true;
            $isOwner = true;
        } elseif ($exam->monetize == 'free') {
            // Free exams are accessible to all
            $hasAccess = true;
        } elseif ($exam->monetize == 'paid') {
            // Check if purchased
            $userType = strtolower(class_basename($user));
            $isPurchased = $exam->isPurchasedBy($user->id, $userType);
            
            if ($isPurchased) {
                $hasAccess = true;
            } else {
                $needsPurchase = true;
            }
        }

        return [
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_owner' => $isOwner,
            'is_purchased' => $isPurchased,
            'is_free' => $exam->monetize == 'free',
            'price' => $exam->price,
            'monetize' => $exam->monetize
        ];
    }

    public function checkCourseAccess($user, Course $course): array
    {
        $hasAccess = false;
        $needsPurchase = false;
        $isOwner = false;
        $isPurchased = false;

        // Check ownership
        if ($user instanceof Teacher && $course->teacher_id == $user->id) {
            $hasAccess = true;
            $isOwner = true;
        } elseif ($user instanceof Institute && $course->institute_id == $user->id) {
            $hasAccess = true;
            $isOwner = true;
        } elseif ($course->monetize == 'free') {
            // Free courses are accessible to all
            $hasAccess = true;
        } elseif ($course->monetize == 'paid') {
            // Check if purchased
            if ($user instanceof Student) {
                $isPurchased = $user->courses()->where('course_id', $course->id)->exists();
            } else {
                $userType = strtolower(class_basename($user));
                $isPurchased = $course->isPurchasedBy($user->id, $userType);
            }
            
            if ($isPurchased) {
                $hasAccess = true;
            } else {
                $needsPurchase = true;
            }
        }

        return [
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_owner' => $isOwner,
            'is_purchased' => $isPurchased,
            'is_free' => $course->monetize == 'free',
            'price' => $course->price,
            'monetize' => $course->monetize
        ];
    }

    public function getUserPurchasedExams($user)
    {
        if ($user instanceof Teacher) {
            return $user->purchasedExams();
        } elseif ($user instanceof Institute) {
            return $user->purchasedExams();
        } elseif ($user instanceof Student) {
            return $user->exams()->wherePivot('is_paid', true);
        }

        return collect();
    }

    public function getUserPurchasedCourses($user)
    {
        if ($user instanceof Teacher) {
            return $user->purchasedCourses();
        } elseif ($user instanceof Institute) {
            return $user->purchasedCourses();
        } elseif ($user instanceof Student) {
            return $user->courses();
        }

        return collect();
    }
}