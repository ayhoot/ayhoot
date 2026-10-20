<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LabelAssignCourseRequest;
use App\Http\Requests\Admin\LabelAssignExamRequest;
use App\Http\Requests\Admin\LabelAssignInstituteRequest;
use App\Http\Requests\Admin\LabelAssignTeacherRequest;
use App\Http\Requests\Admin\LabelRemoveExamRequest;
use App\Http\Requests\Admin\LabelStoreRequest;
use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::withCount('exams')->get();
        return response($labels);
    }

    public function store(LabelStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Move to public/labels
            $file->move(public_path('uploads/labels'), $filename);
            $data['logo'] = 'uploads/labels/' . $filename;
        }

        $label = Label::create($data);

        return response([
            'message' => __('messages.label_created_successfully'),
            'label' => $label
        ], 200);
    }

    public function show(Label $label)
    {
        $label->load('exams');
        return response($label);
    }

    public function update(LabelStoreRequest $request, Label $label)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            if ($label->logo && file_exists(public_path($label->logo))) {
                unlink(public_path($label->logo));
            }
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/labels'), $filename);
            $data['logo'] = 'uploads/labels/' . $filename;
        }

        $label->update($data);
        return response(['message' => __('messages.label_updated_successfully'), 'label' => $label]);
    }

    public function destroy(Label $label)
    {
        if ($label->id == 1) {
            return response([
                'message' => __('messages.label_cannot_be_deleted')
            ], 403);
        }

        // Delete the associated image
        if ($label->logo && file_exists(public_path($label->logo))) {
            unlink(public_path($label->logo));
        }

        $label->delete();

        return response(['message' => __('messages.label_deleted_successfully')]);
    }

    public function assignExam(LabelAssignExamRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->exams()->syncWithoutDetaching([$data['exam_id']]);
        return response(['message' => __('messages.exam_assigned_to_label_successfully')]);
    }

    public function removeExam(LabelRemoveExamRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->exams()->detach($data['exam_id']);
        return response(['message' => __('messages.exam_removed_from_label_successfully')]);
    }




    public function assignTeacher(LabelAssignTeacherRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->teachers()->syncWithoutDetaching([$data['teacher_id']]);
        return response(['message' => __('messages.teacher_assigned_to_label_successfully')]);
    }

    public function removeTeacher(LabelAssignTeacherRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->teachers()->detach($data['teacher_id']);
        return response(['message' => __('messages.teacher_removed_from_label_successfully')]);
    }



    public function assignInstitute(LabelAssignInstituteRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->institutes()->syncWithoutDetaching([$data['institute_id']]);
        return response(['message' => __('messages.institute_assigned_to_label_successfully')]);
    }

    public function removeInstitute(LabelAssignInstituteRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->institutes()->detach($data['institute_id']);
        return response(['message' => __('messages.institute_removed_from_label_successfully')]);
    }




    public function assignCourse(LabelAssignCourseRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->courses()->syncWithoutDetaching([$data['course_id']]);
        return response(['message' => __('messages.course_assigned_to_label_successfully')]);
    }

    public function removeCourse(LabelAssignCourseRequest $request, Label $label)
    {
        $data = $request->validated();
        $label->courses()->detach($data['course_id']);
        return response(['message' => __('messages.course_removed_from_label_successfully')]);
    }
}
