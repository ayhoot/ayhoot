<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Requests\Shared\AddFileOrVideoRequest;
use App\Http\Requests\Shared\AssignExamsToCourseRequest;
use App\Http\Requests\Student\AddFileOrVideoRequest as StudentAddFileOrVideoRequest;
use App\Http\Requests\Teacher\AssignExamsToCourseRequest as TeacherAssignExamsToCourseRequest;
use App\Models\Course;
use App\Models\Folder;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait CourseLogic
{
    abstract protected function getAuthenticatedUser();
    abstract protected function getRoleForeignKey();
    abstract protected function getStoreRequestClass();
    abstract protected function getUpdateRequestClass();

    public function index()
    {
        $user = $this->getAuthenticatedUser();
        $query = Course::query()
            ->where($this->getRoleForeignKey(), $user->id)
            ->with(['institute', 'teacher', 'exams', 'category', 'labels'])
            ->withCount('students')
            ->orderBy('created_at', 'desc');

        if ($this->getRoleForeignKey() == 'teacher_id') {
            $selected_institute = $user->selected_institute;
            $query->where('institute_id', $selected_institute);
        }

        return response(['courses' => $query->get()]);
    }

    public function store(Request $request)
    {
        $request->validate(app($this->getStoreRequestClass())->rules());

        $data = $request->all();
        $user = $this->getAuthenticatedUser();
        $data[$this->getRoleForeignKey()] = $user->id;

        if ($this->getRoleForeignKey() == 'teacher_id') {
            $data['institute_id'] = $user->selected_institute;
        }

        $course = Course::create($data);
        return response(['message' => __('messages.course_created_successfully'), 'course' => $course], 201);
    }

    public function show(Course $course)
    {
        $this->authorizeAction($course);
        return response(['course' => $course->load(['institute', 'teacher', 'exams', 'category', 'labels'])->loadCount('students')]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeAction($course);
        $request->validate(app($this->getUpdateRequestClass())->rules());

        $data = $request->all();
        if ($request->hasFile('cover')) {
            if ($course->cover) Storage::disk('public')->delete($course->cover);
            $data['cover'] = $request->file('cover')->store('courses', 'public');
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
        } else {
            throw ValidationException::withMessages(['file' => __('messages.file_or_url_required')]);
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
        $this->authorizeAction($course);
        $newCourse = $course->replicate();
        $newCourse->title = $course->title . ' (Copy)';
        $newCourse->created_at = now();
        $newCourse->updated_at = now();
        $newCourse->{$this->getRoleForeignKey()} = $this->getAuthenticatedUser()->id;
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
                $newAssignedExamIds[] = $item['id'];
            }
            $newTimeline[] = $newItem;
        }
        $newCourse->options = json_encode(['timeline' => $newTimeline]);
        $newCourse->save();
        if (!empty($newAssignedExamIds)) $newCourse->exams()->sync($newAssignedExamIds);
        return response(['message' => __('messages.course_duplicated_successfully'), 'course' => $newCourse], 201);
    }

    protected function authorizeAction($model, $modelName = 'course')
    {
        if ($model->{$this->getRoleForeignKey()} != $this->getAuthenticatedUser()->id) {
            abort(403, __("messages.unauthorized_{$modelName}"));
        }
    }
}
