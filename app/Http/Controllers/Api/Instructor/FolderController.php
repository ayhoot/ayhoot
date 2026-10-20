<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Api\Instructor\Concerns\InteractsWithInstructor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AssignExamsToFolderRequest;
use App\Http\Requests\Teacher\StoreFolderRequest;
use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    use InteractsWithInstructor;

    public function index()
    {
        $instructor = $this->getInstructor();

        $query = Folder::query()
            ->where($this->ownerColumn(), $instructor->id)
            ->with(['exams', 'courses']);

        // Teachers scope folders to the institute they currently have selected;
        // institutes own their folders directly and have no such scoping.
        if ($this->isTeacher()) {
            $query->where('institute_id', $instructor->selected_institute);
        }

        return response(['folders' => $query->get()]);
    }

    public function store(StoreFolderRequest $request)
    {
        $instructor = $this->getInstructor();

        $attributes = [
            'name' => $request->name,
            $this->ownerColumn() => $instructor->id,
        ];

        // A teacher's folder is additionally tied to their selected institute
        // (null if none selected). For an institute, teacher_id stays null.
        if ($this->isTeacher()) {
            $attributes['institute_id'] = $instructor->selected_institute;
        }

        $folder = Folder::create($attributes);

        return response(['message' => __('messages.folder_created_successfully'), 'folder' => $folder], 201);
    }

    public function show(Folder $folder)
    {
        $this->authorizeFolder($folder);

        return response(['folder' => $folder->load(['exams', 'courses'])]);
    }

    public function delete(Folder $folder)
    {
        $this->authorizeFolder($folder);

        $folder->delete();

        return response(['message' => __('messages.folder_deleted_successfully')]);
    }

    public function assignExamsToFolder(AssignExamsToFolderRequest $request, Folder $folder)
    {
        $this->authorizeFolder($folder);

        $validated = $request->validated();
        $folder->exams()->syncWithoutDetaching($validated['exam_ids']);

        return response(['message' => __('messages.exams_assigned_to_folder_successfully'), 'folder' => $folder->load('exams')], 200);
    }

    public function unassignExamsFromFolder(Request $request, Folder $folder)
    {
        $this->authorizeFolder($folder);

        $request->validate(['exam_ids' => 'required|array', 'exam_ids.*' => 'integer|exists:exams,id']);
        $folder->exams()->detach($request->exam_ids);

        return response(['message' => __('messages.exams_unassigned_from_folder_successfully'), 'folder' => $folder->load('exams')], 200);
    }

    public function getCoursesInFolder(Folder $folder)
    {
        $this->authorizeFolder($folder);

        return response(['courses' => $folder->courses()->with(['exams', 'category'])->get()]);
    }

    public function unassignCoursesFromFolder(Request $request, Folder $folder)
    {
        $this->authorizeFolder($folder);

        $request->validate(['course_ids' => 'required|array', 'course_ids.*' => 'integer|exists:courses,id']);
        $folder->courses()->detach($request->course_ids);

        return response(['message' => __('messages.courses_unassigned_from_folder_successfully')]);
    }

    /**
     * Ensure the folder belongs to the current instructor, aborting with 403 otherwise.
     */
    protected function authorizeFolder(Folder $folder): void
    {
        if ($folder->{$this->ownerColumn()} != $this->getInstructor()->id) {
            abort(403, __('messages.unauthorized_folder_access'));
        }
    }
}
