<?php

namespace App\Http\Controllers\Api\Instructor\Concerns;

/**
 * Shared helpers for the unified Instructor controllers.
 *
 * A "instructor" is either a Teacher or an Institute. Both guards are checked so
 * a single controller can serve both roles. Ownership columns on related models
 * follow the "{type}_id" convention (teacher_id / institute_id), so the dynamic
 * column name from getInstructorType() can be used directly in queries.
 */
trait InteractsWithInstructor
{
    /**
     * The authenticated instructor (Teacher or Institute model).
     */
    protected function getInstructor()
    {
        $user = auth('teacher')->user() ?? auth('institute')->user() ?? auth('sanctum')->user() ?? auth()->user();
        if ($user instanceof \App\Models\Teacher || $user instanceof \App\Models\Institute) {
            return $user;
        }
        return null;
    }

    /**
     * The instructor type: "teacher" or "institute" (null if unauthenticated).
     */
    protected function getInstructorType(): ?string
    {
        $user = $this->getInstructor();
        if ($user instanceof \App\Models\Teacher) {
            return 'teacher';
        }
        if ($user instanceof \App\Models\Institute) {
            return 'institute';
        }
        return null;
    }

    /**
     * The ownership foreign-key column for the current instructor, e.g. "teacher_id".
     */
    protected function ownerColumn(): string
    {
        return $this->getInstructorType() . '_id';
    }

    /**
     * True when the current instructor is an institute.
     */
    protected function isInstitute(): bool
    {
        return $this->getInstructorType() === 'institute';
    }

    /**
     * True when the current instructor is a teacher.
     */
    protected function isTeacher(): bool
    {
        return $this->getInstructorType() === 'teacher';
    }

    /**
     * Abort with 403 unless the current instructor is a teacher.
     * Use on teacher-only endpoints (e.g. joining an institute) so the wrong
     * role gets a clean 403 instead of a 500 from a missing model relation.
     */
    protected function abortUnlessTeacher(): void
    {
        abort_unless($this->isTeacher(), 403, __('messages.unauthorized'));
    }

    /**
     * Abort with 403 unless the current instructor is an institute.
     * Use on institute-only endpoints (e.g. managing teachers).
     */
    protected function abortUnlessInstitute(): void
    {
        abort_unless($this->isInstitute(), 403, __('messages.unauthorized'));
    }
}
