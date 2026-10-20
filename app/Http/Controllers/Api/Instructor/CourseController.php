<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Api\Instructor\Concerns\InteractsWithInstructor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\AddFileOrVideoRequest;
use App\Http\Requests\Shared\AssignExamsToCourseRequest;
use App\Http\Requests\Shared\StoreCourseRequest;
use App\Http\Requests\Shared\UpdateCourseRequest;
use App\Http\Requests\Student\AddFileOrVideoRequest as StudentAddFileOrVideoRequest;
use App\Http\Requests\Teacher\AssignExamsToCourseRequest as TeacherAssignExamsToCourseRequest;
use App\Http\Requests\Teacher\CourseContentRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Folder;
use App\Models\Invoice;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    use InteractsWithInstructor;

    public function index(Request $request)
    {
        $user = $this->getInstructor();
        $query = Course::query()
            ->where($this->ownerColumn(), $user->id)
            ->where('archive', null)
            ->with(['institute', 'teacher', 'exams', 'category', 'labels'])
            ->withCount(['students', 'exams'])
            ->orderBy('created_at', 'desc');

        // Teachers scope by their current context: an institute is selected ->
        // only that institute's courses; none selected (standalone) -> only their
        // own non-institute courses.
        if ($this->isTeacher()) {
            $selected_institute = $user->selected_institute;
            if ($selected_institute) {
                $query->where('institute_id', $selected_institute);
            } else {
                $query->whereNull('institute_id');
            }
        }

        if ($request->has('purchased')) {
            $query->where('purchased', 'yes');
        }

        return response(['courses' => $query->paginate(10)]);
    }

    public function store(StoreCourseRequest $request)
    {
        $user = $this->getInstructor();

        // Check permission using the service
        $permissionService = app(\App\Services\PermissionService::class);
        $canCreate = $permissionService->canCreateCourse($user);

        if (!$canCreate['allowed']) {
            return response([
                'message' => $canCreate['reason'],
                'permission_denied' => true,
                'details' => $canCreate
            ], 403);
        }

        $data = $request->validated();
        $data[$this->ownerColumn()] = $user->id;

        // A teacher additionally links the course to their selected institute.
        if ($this->isTeacher()) {
            $data['institute_id'] = $user->selected_institute;
        }

        $course = Course::create($data);

        return response([
            'message' => __('messages.course_created_successfully'),
            'course' => $course,
            'permission_info' => $canCreate
        ], 201);
    }

    public function show(Course $course)
    {
        $user = $this->getInstructor();

        // Institute version wraps loading in try/catch with logging.
        if ($this->isInstitute()) {
            try {
                \Log::info('Institute course show request', [
                    'course_id' => $course->id,
                    'institute_id' => $user->id
                ]);

                // Load course with relationships
                $course->load(['institute', 'teacher', 'exams', 'category', 'labels'])->loadCount('students');

                $isOwner = ($course->{$this->ownerColumn()} == $user->id);

                // Use unified access control
                $hasAccess = $course->hasAccessFor($user);
                $needsPurchase = !$hasAccess && $course->monetize == 'paid';
                $purchased = $hasAccess ? 'yes' : 'no';

                // Add purchase status to course object
                $course->purchased = $purchased;

                return response([
                    'course' => $course,
                    'has_access' => $hasAccess,
                    'needs_purchase' => $needsPurchase,
                    'is_owner' => $isOwner,
                    'is_free' => $course->monetize == 'free'
                ]);
            } catch (\Exception $e) {
                \Log::error('Error in Institute CourseController show method', [
                    'course_id' => $course->id ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response(['message' => 'Failed to load course'], 500);
            }
        }

        $isOwner = ($course->{$this->ownerColumn()} == $user->id);

        // Use unified access control
        $hasAccess = $course->hasAccessFor($user);
        $needsPurchase = !$hasAccess && $course->monetize == 'paid';
        $purchased = $hasAccess ? 'yes' : 'no';

        // Add purchase status to course object
        $course->purchased = $purchased;

        return response([
            'course' => $course->load(['institute', 'teacher', 'exams', 'category', 'labels'])->loadCount('students'),
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_owner' => $isOwner,
            'is_free' => $course->monetize == 'free'
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $this->authorizeAction($course);
        $data = $request->validated();
        if ($request->hasFile('cover')) {
            if ($course->cover) Storage::disk('public')->delete($course->cover);
            $data['cover'] = $request->file('cover')->store('courses', 'public');
        } elseif ($request->has('remove_cover') && $request->remove_cover == '1') {
            // Remove existing cover
            if ($course->cover) Storage::disk('public')->delete($course->cover);
            $data['cover'] = null;
        }
        $course->update($data);
        return response(['message' => __('messages.course_updated_successfully'), 'course' => $course]);
    }

    public function delete(Course $course)
    {
        $this->authorizeAction($course);
        $course->delete();
        return response(['message' => __('messages.course_deleted_successfully')]);
    }

    public function archive(Course $course)
    {
        $this->authorizeAction($course);
        $course->archive = ($course->archive == 'yes' ? 'no' : 'yes');
        $course->save();
        return response(['message' => __('messages.course_status_updated_successfully'), 'course' => $course]);
    }

    public function assignExamsToCourse(TeacherAssignExamsToCourseRequest $request, Course $course)
    {
        $this->authorizeAction($course);
        $examIds = $request->validated()['exam_ids'];
        $course->exams()->syncWithoutDetaching($examIds);
        $options = json_decode($course->options, true) ?? ['timeline' => []];
        foreach ($examIds as $id) {
            $options['timeline'][] = ['type' => 'exam', 'id' => $id];
        }
        $course->update(['options' => json_encode($options)]);
        return response(['message' => __('messages.exams_assigned_to_course_successfully'), 'course' => $course->load('exams')], 200);
    }

    public function addFileOrVideo(StudentAddFileOrVideoRequest $request, Course $course)
    {
        $this->authorizeAction($course);
        $options = json_decode($course->options, true) ?? ['timeline' => []];
        $timestamp = now()->toDateTimeString();
        $item = ['title' => $request->title, 'type' => $request->type];

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('courses', 'public');
            $item['path'] = $path;
            $item['uploaded_at'] = $timestamp;
        } elseif ($request->type == 'video' && $request->filled('video_url')) {
            $item['url'] = $request->video_url;
            $item['added_at'] = $timestamp;
        }

        $options['timeline'][] = $item;
        $course->update(['options' => json_encode($options)]);
        return response(['message' => __('messages.content_added_to_course_timeline_successfully'), 'timeline' => $options['timeline']]);
    }

    public function removeContent(Request $request, Course $course)
    {
        $this->authorizeAction($course);
        $request->validate(['content_index' => 'required|integer|min:0']);
        $options = json_decode($course->options, true) ?? ['timeline' => []];
        if (!isset($options['timeline'][$request->content_index])) {
            return response(['message' => __('messages.content_not_found')], 409);
        }
        $content = $options['timeline'][$request->content_index];
        if (isset($content['path'])) {
            $filePath = str_replace(Storage::url(''), '', $content['path']);
            Storage::disk('public')->delete($filePath);
        }
        array_splice($options['timeline'], $request->content_index, 1);
        $course->update(['options' => json_encode($options)]);
        return response(['message' => __('messages.content_removed_successfully'), 'timeline' => $options['timeline']]);
    }

    public function removeExamFromCourse(Course $course, Exam $exam)
    {
        $this->authorizeAction($course);
        $course->exams()->detach($exam->id);
        $options = json_decode($course->options, true) ?? ['timeline' => []];
        $options['timeline'] = array_values(array_filter($options['timeline'], fn($item) => !($item['type'] == 'exam' && $item['id'] == $exam->id)));
        $course->update(['options' => json_encode($options)]);
        return response(['message' => __('messages.exam_removed_from_course_successfully'), 'course' => $course->load('exams')]);
    }

    public function moveToFolder(Course $course, Folder $folder)
    {
        $this->authorizeAction($course);
        $this->authorizeAction($folder, 'folder');
        $course->folders()->syncWithoutDetaching([$folder->id]);
        return response(['message' => __('messages.course_moved_to_folder_successfully'), 'course' => $course->load('folders')]);
    }

    public function removeCourseFromFolder(Course $course, Folder $folder)
    {
        $this->authorizeAction($course);
        $this->authorizeAction($folder, 'folder');
        $course->folders()->detach($folder->id);
        return response(['message' => __('messages.course_removed_from_folder_successfully'), 'course' => $course->load('folders')]);
    }

    public function updateContent(Request $request, Course $course)
    {
        $this->authorizeAction($course);

        // The two source versions validate differently:
        // - Teacher used a dedicated CourseContentRequest form request.
        // - Institute validated inline on the incoming Request.
        // Both ultimately enforce the same rules, so we apply the inline rules
        // (a superset that works regardless of guard) to keep behavior identical.
        $request->validate([
            'content_index' => 'required|integer|min:0',
            'title' => 'required|string|max:255',
            'type' => 'required|in:file,video',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,mov,avi|max:102400',
            'video_url' => 'nullable|max:2048',
        ]);

        $options = json_decode($course->options, true) ?? ['timeline' => []];
        $contentIndex = $request->content_index;
        if (!isset($options['timeline'][$contentIndex])) {
            return response(['message' => __('messages.content_not_found')], 409);
        }
        $content = &$options['timeline'][$contentIndex];
        $content['title'] = $request->title;
        $content['type'] = $request->type;
        if ($request->hasFile('file')) {
            if (isset($content['path'])) Storage::disk('public')->delete(str_replace(Storage::url(''), '', $content['path']));
            $path = $request->file('file')->store('courses', 'public');
            $content['path'] = $path;
            if (isset($content['url'])) unset($content['url']);
        } elseif ($request->type == 'video' && $request->filled('video_url')) {
            $content['url'] = $request->video_url;
            if (isset($content['path'])) {
                Storage::disk('public')->delete(str_replace(Storage::url(''), '', $content['path']));
                unset($content['path']);
            }
        }
        $content['updated_at'] = now()->toDateTimeString();
        $course->update(['options' => json_encode($options)]);
        return response(['message' => __('messages.content_updated_successfully'), 'timeline' => $options['timeline']]);
    }

    public function duplicate(Course $course)
    {
        $user = $this->getInstructor();

        // Check if user owns the course OR has purchased it
        $canDuplicate = false;

        if ($course->{$this->ownerColumn()} == $user->id) {
            // User owns the course
            $canDuplicate = true;
        } elseif ($course->hasAccessFor($user)) {
            // User has purchased the course
            $canDuplicate = true;
        }

        if (!$canDuplicate) {
            abort(403, __('messages.unauthorized_course'));
        }

        $newCourse = $course->replicate();
        $newCourse->title = $course->title . ' (Copy)';
        $newCourse->created_at = now();
        $newCourse->updated_at = now();
        $newCourse->{$this->ownerColumn()} = $user->id;

        // Ownership linkage differs by instructor type:
        // - Teacher attaches the copy to their selected institute.
        // - Institute clears teacher_id since the institute is the copier.
        if ($this->isTeacher()) {
            $newCourse->institute_id = $user->selected_institute;
        } else {
            $newCourse->teacher_id = null;
        }

        $originalOptions = json_decode($course->options, true) ?? ['timeline' => []];
        $newTimeline = [];
        $newAssignedExamIds = [];

        foreach ($originalOptions['timeline'] as $item) {
            $newItem = $item;
            if (in_array($item['type'], ['file', 'video']) && isset($item['path'])) {
                $originalFilePath = str_replace(Storage::url(''), '', $item['path']);
                if (Storage::disk('public')->exists($originalFilePath)) {
                    $newFileName = 'courses/' . uniqid() . '_' . basename($originalFilePath);
                    Storage::disk('public')->copy($originalFilePath, $newFileName);
                    $newItem['path'] = Storage::url($newFileName);
                } else {
                    $newItem['path'] = null;
                }
            } elseif ($item['type'] == 'exam' && isset($item['id'])) {
                // Only copy exams that the user has access to
                $exam = \App\Models\Exam::find($item['id']);
                if ($exam && $exam->hasAccessFor($user)) {
                    $newAssignedExamIds[] = $item['id'];
                }
            }
            $newTimeline[] = $newItem;
        }

        $newCourse->options = json_encode(['timeline' => $newTimeline]);
        $newCourse->save();
        if (!empty($newAssignedExamIds)) $newCourse->exams()->sync($newAssignedExamIds);

        // Teacher source returned 201, institute source returned 200; preserve each.
        $status = $this->isTeacher() ? 201 : 200;

        return response(['message' => __('messages.course_duplicated_successfully'), 'course' => $newCourse], $status);
    }

    public function purchased(Request $request)
    {
        $user = $this->getInstructor();
        $query = Course::query()
            ->where($this->ownerColumn(), $user->id)
            ->where('purchased', 'yes')
            ->with(['institute', 'teacher', 'exams', 'category', 'labels'])
            ->withCount('students')
            ->orderBy('created_at', 'desc');
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->has('monetize')) {
            $query->where('monetize', $request->monetize);
        }

        return response(['courses' => $query->paginate(10)]);
    }

    protected function authorizeAction($model, $modelName = 'course')
    {
        $foreignKey = $this->ownerColumn();
        if ($model->{$foreignKey} != $this->getInstructor()->id) {
            abort(403, __("messages.unauthorized_{$modelName}"));
        }
    }
}
