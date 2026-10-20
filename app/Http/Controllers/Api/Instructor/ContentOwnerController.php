<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Base controller for content owners (Teachers and Institutes)
 * Contains shared functionality between Teacher and Institute controllers
 */
abstract class ContentOwnerController extends Controller
{
    /**
     * Get the authenticated user (Teacher or Institute)
     */
    protected function getAuthenticatedUser()
    {
        if (auth('teacher')->check()) {
            return auth('teacher')->user();
        } elseif (auth('institute')->check()) {
            return auth('institute')->user();
        }
        return null;
    }

    /**
     * Get user type string
     */
    protected function getUserType($user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        if ($user instanceof Teacher) return 'teacher';
        if ($user instanceof Institute) return 'institute';
        return null;
    }

    /**
     * Check if user owns the content
     */
    protected function ownsContent($content, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        if ($user instanceof Teacher) {
            return $content->teacher_id == $user->id;
        } elseif ($user instanceof Institute) {
            return $content->institute_id == $user->id;
        }
        
        return false;
    }

    /**
     * Get content access information for owner
     */
    protected function getContentAccess($content, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        $isOwner = $this->ownsContent($content, $user);
        
        // Use unified access control
        $hasAccess = $content->hasAccessFor($user);
        $needsPurchase = !$hasAccess && $content->monetize == 'paid';
        $isPurchased = $hasAccess && !$isOwner && $content->monetize == 'paid';

        return [
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_owner' => $isOwner,
            'is_purchased' => $isPurchased,
            'is_free' => $content->monetize == 'free',
            'price' => $content->price,
            'monetize' => $content->monetize
        ];
    }

    /**
     * Get owned content query
     */
    protected function getOwnedContentQuery($contentType, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        if ($contentType == 'exam') {
            $query = Exam::query();
        } elseif ($contentType == 'course') {
            $query = Course::query();
        } else {
            throw new \InvalidArgumentException('Invalid content type');
        }

        if ($user instanceof Teacher) {
            $query->where('teacher_id', $user->id);
            // Display is scoped by the teacher's current context: when an institute
            // is selected, show only that institute's content; when none is selected
            // (standalone), show only their own non-institute content.
            if ($user->selected_institute) {
                $query->where('institute_id', $user->selected_institute);
            } else {
                $query->whereNull('institute_id');
            }
        } elseif ($user instanceof Institute) {
            $query->where('institute_id', $user->id);
        }

        return $query;
    }

    /**
     * Get purchased content for user
     */
    protected function getPurchasedContent($contentType, $user = null, $request = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);
        
        if ($contentType == 'exam') {
            $query = $user->purchasedExams()
                ->withCount(['questions', 'students'])
                ->with(['teacher', 'institute', 'category']);
        } elseif ($contentType == 'course') {
            $query = $user->purchasedCourses()
                ->withCount(['exams'])
                ->with(['teacher', 'institute', 'category']);
        } else {
            throw new \InvalidArgumentException('Invalid content type');
        }

        // Apply search filter if provided
        if ($request && $request->search) {
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%'));
        }

        return $query;
    }

    /**
     * Get discoverable paid content
     */
    protected function getDiscoverablePaidContent($contentType, $user = null, $request = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        if ($contentType == 'exam') {
            $query = Exam::where('monetize', 'paid')
                ->where('status', 'published')
                ->withCount(['questions', 'students'])
                ->with(['teacher', 'institute', 'category']);
        } elseif ($contentType == 'course') {
            $query = Course::where('monetize', 'paid')
                ->where('status', 'published')
                ->withCount(['exams'])
                ->with(['teacher', 'institute', 'category']);
        } else {
            throw new \InvalidArgumentException('Invalid content type');
        }

        // Exclude own content
        if ($user instanceof Teacher) {
            $query->where(fn($q) => $q->whereNull('teacher_id')->orWhere('teacher_id', '!=', $user->id));
        } elseif ($user instanceof Institute) {
            $query->where(fn($q) => $q->whereNull('institute_id')->orWhere('institute_id', '!=', $user->id));
        }

        // Apply filters if provided
        if ($request) {
            if ($request->search) {
                $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%'));
            }

            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Add purchase status to content collection
     */
    protected function addPurchaseStatus($content, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        $userType = $this->getUserType($user);

        if ($content instanceof \Illuminate\Database\Eloquent\Collection) {
            return $content->transform(function ($item) use ($user, $userType) {
                $item->is_purchased = $item->isPurchasedBy($user->id, $userType);
                return $item;
            });
        } else {
            $content->is_purchased = $content->isPurchasedBy($user->id, $userType);
            return $content;
        }
    }

    /**
     * Validate ownership for modification operations
     */
    protected function validateOwnership($content, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        if (!$this->ownsContent($content, $user)) {
            abort(403, __('messages.unauthorized_access'));
        }
    }

    /**
     * Get content creation data
     */
    protected function getContentCreationData($data, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        if ($user instanceof Teacher) {
            $data['teacher_id'] = $user->id;
            if ($user->selected_institute) {
                $data['institute_id'] = $user->selected_institute;
            }
        } elseif ($user instanceof Institute) {
            $data['institute_id'] = $user->id;
        }

        return $data;
    }

    /**
     * Handle file upload for content
     */
    protected function handleFileUpload($request, $fieldName, $directory, $existingFile = null)
    {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        // Delete existing file if provided
        if ($existingFile && Storage::disk('public')->exists($existingFile)) {
            Storage::disk('public')->delete($existingFile);
        }

        return $request->file($fieldName)->store($directory, 'public');
    }

    /**
     * Duplicate content with proper ownership
     */
    protected function duplicateContent($originalContent, $user = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        
        // Validate ownership
        $this->validateOwnership($originalContent, $user);
        
        // Check if content is paid (can't duplicate paid content you don't own)
        if ($originalContent->monetize == 'paid' && !$this->ownsContent($originalContent, $user)) {
            abort(403, __('messages.paid_content_access_denied'));
        }

        // Create duplicate
        $duplicate = $originalContent->replicate();
        $duplicate->title = $originalContent->title . ' (Copy)';
        
        // Set ownership
        if ($user instanceof Teacher) {
            $duplicate->teacher_id = $user->id;
            $duplicate->institute_id = $user->selected_institute;
        } elseif ($user instanceof Institute) {
            $duplicate->institute_id = $user->id;
            $duplicate->teacher_id = null;
        }

        $duplicate->created_at = now();
        $duplicate->updated_at = now();

        // Handle cover image duplication
        if ($originalContent->cover) {
            $originalCoverPath = $originalContent->cover;
            $newCoverPath = $this->duplicateFile($originalCoverPath);
            if ($newCoverPath) {
                $duplicate->cover = $newCoverPath;
            }
        }

        $duplicate->save();

        return $duplicate;
    }

    /**
     * Duplicate a file
     */
    protected function duplicateFile($originalPath)
    {
        if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
            return null;
        }

        $directory = dirname($originalPath);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $newPath = $directory . '/' . $filename . '_' . uniqid() . '.' . $extension;

        if (Storage::disk('public')->copy($originalPath, $newPath)) {
            return $newPath;
        }

        return null;
    }

    /**
     * Get combined purchased content (exams and courses)
     */
    protected function getCombinedPurchasedContent($user = null, $request = null)
    {
        $user = $user ?: $this->getAuthenticatedUser();
        $contentType = $request ? $request->get('type', 'all') : 'all';
        
        $purchasedExams = collect();
        $purchasedCourses = collect();
        
        // Get content based on filter
        if ($contentType == 'all' || $contentType == 'exams') {
            $purchasedExams = $this->getPurchasedContent('exam', $user, $request)->get();
        }
        
        if ($contentType == 'all' || $contentType == 'courses') {
            $purchasedCourses = $this->getPurchasedContent('course', $user, $request)->get();
        }
        
        // Combine and format
        $allPurchasedContent = collect();
        
        if ($contentType == 'all' || $contentType == 'exams') {
            $allPurchasedContent = $allPurchasedContent->merge(
                $purchasedExams->map(function ($exam) {
                    $exam->content_type = 'exam';
                    $exam->purchased_at = $exam->pivot->purchased_at ?? $exam->created_at;
                    $exam->icon = 'exam';
                    $exam->type_label = 'Exam';
                    return $exam;
                })
            );
        }
        
        if ($contentType == 'all' || $contentType == 'courses') {
            $allPurchasedContent = $allPurchasedContent->merge(
                $purchasedCourses->map(function ($course) {
                    $course->content_type = 'course';
                    $course->purchased_at = $course->pivot->purchased_at ?? $course->created_at;
                    $course->icon = 'course';
                    $course->type_label = 'Course';
                    return $course;
                })
            );
        }
        
        return $allPurchasedContent->sortByDesc('purchased_at')->values();
    }
}