<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Requests\Shared\StoreExamRequest;
use App\Http\Requests\Shared\UpdateExamRequest;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ExamLogic
{
    abstract protected function getAuthenticatedUser();
    abstract protected function getRoleForeignKey();
    abstract protected function getStoreRequestClass();
    abstract protected function getUpdateRequestClass();

    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = Exam::query()
            ->where($this->getRoleForeignKey(), $user->id)
            ->withCount(['questions', 'students'])
            ->with(['teacher', 'institute'])
            ->orderBy('created_at', 'desc');

        if ($this->getRoleForeignKey() == 'teacher_id') {
            $query->where('institute_id', $user->selected_institute);
        }

        if ($request->search) {
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%'));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response(['exams' => $query->paginate(10)]);
    }

    public function show(Exam $exam)
    {
        $this->authorizeAction($exam);
        return response(['exam' => $exam->load('questions')]);
    }

    public function store(Request $request)
    {
        $request->validate(app($this->getStoreRequestClass())->rules());
        $data = $request->all();
        $user = $this->getAuthenticatedUser();

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('exams', 'public');
        }

        $exam = new Exam($data);
        $exam->{$this->getRoleForeignKey()} = $user->id;

        if ($this->getRoleForeignKey() == 'teacher_id') {
            $exam->institute_id = $user->selected_institute;
        }

        $exam->save();
        return response(['exam' => $exam, 'message' => __('messages.exam_created_successfully')], 201);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorizeAction($exam);
        $request->validate(app($this->getUpdateRequestClass())->rules());
        $data = $request->all();
        if ($request->hasFile('cover')) {
            if ($exam->cover) Storage::disk('public')->delete($exam->cover);
            $data['cover'] = $request->file('cover')->store('exams', 'public');
        }
        $exam->update($data);
        return response(['message' => __('messages.exam_updated_successfully'), 'exam' => $exam]);
    }

    public function delete(Exam $exam)
    {
        $this->authorizeAction($exam);
        $exam->delete();
        return response(['message' => __('messages.exam_deleted_successfully')]);
    }

    public function questions(Exam $exam)
    {
        $this->authorizeAction($exam);
        return response(['questions' => $exam->questions]);
    }

    public function archives(Request $request)
    {
        $query = Exam::query()
            ->where($this->getRoleForeignKey(), $this->getAuthenticatedUser()->id)
            ->whereNotNull('archive')
            ->with(['teacher', 'institute']);
        
        if ($request->search) {
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%'));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response(['exams' => $query->latest()->paginate(10)]);
    }

    public function archive(Exam $exam)
    {
        $this->authorizeAction($exam);
        $exam->archive = $exam->archive ? null : 'yes';
        $exam->save();
        return response(['message' => __('messages.exam_status_updated_successfully'), 'exam' => $exam]);
    }

    public function duplicate(Exam $exam)
    {
        $this->authorizeAction($exam);
        $user = $this->getAuthenticatedUser();
        $roleKey = $this->getRoleForeignKey();

        if ($exam->monetize == 'paid') {
            return response(['message' => __('messages.paid_exam_access_denied')], 403);
        }

        $newExam = $exam->replicate(['id']);
        $newExam->title = $exam->title . ' (Copy)';
        $newExam->{$roleKey} = $user->id;
        $newExam->created_at = now();
        $newExam->updated_at = now();
        
        if ($exam->cover) {
            $originalCoverPath = str_replace('storage/', '', $exam->cover);
            $newCoverPath = 'exams/' . uniqid() . '_' . basename($originalCoverPath);
            if (Storage::disk('public')->exists($originalCoverPath)) {
                Storage::disk('public')->copy($originalCoverPath, $newCoverPath);
                $newExam->cover = $newCoverPath;
            }
        }
        
        $newExam->save();

        foreach ($exam->questions as $question) {
            $newQuestion = $question->replicate(['id', 'exam_id']);
            $newQuestion->exam_id = $newExam->id;
            $newQuestion->{$roleKey} = $user->id;
            
            if($question->files) {
                // This part needs careful implementation based on your file structure
            }

            if($question->options) {
                // This part also needs careful implementation for image duplication
            }

            $newQuestion->save();
        }

        return response([
            'message' => __('messages.exam_duplicated_successfully'),
            'exam' => $newExam,
        ], 201);
    }
    
    protected function authorizeAction(Exam $exam)
    {
        if ($exam->{$this->getRoleForeignKey()} != $this->getAuthenticatedUser()->id) {
            abort(403, __('messages.unauthorized_exam'));
        }
    }
}