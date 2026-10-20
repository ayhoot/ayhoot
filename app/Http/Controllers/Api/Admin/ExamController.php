<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Http\Requests\Admin\UpdateStatusExamRequest;
use App\Models\Exam;
use App\Models\Notification;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::query()
            ->with(['teacher', 'institute', 'category','labels'])
            ->withCount(['questions', 'students']); // Efficiently get counts

        $query->when($request->search, function ($q, $search) {
            $q->where('title', 'like', "%{$search}%");
        });

        $query->when($request->status, function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($request->accessibility, function ($q, $accessibility) {
            $q->where('monetize', $accessibility);
        });

        $query->when($request->plays, function ($q, $range) {
            if (str_contains($range, '+')) {
                $min = (int) $range;
                $q->having('students_count', '>=', $min);
            } else {
                [$min, $max] = explode('-', $range);
                $q->havingBetween('students_count', [$min, $max]);
            }
        });

        $query->when($request->questions, function ($q, $range) {
            if (str_contains($range, '+')) {
                $min = (int) $range;
                $q->having('questions_count', '>=', $min);
            } else {
                [$min, $max] = explode('-', $range);
                $q->havingBetween('questions_count', [$min, $max]);
            }
        });

        $sortBy = $request->input('sort', 'created_at');
        $sortDirection = $sortBy == 'created_at' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDirection);

        $exams = $query->paginate(10)->appends($request->all());

        return response(['exams' => $exams]);
    }


    public function edit(Exam $exam)
    {
        return response([
            'exam' => $exam->load(['teacher', 'institute', 'category','labels','questions'])
        ]);
    }

    /**
     * Return an exam's questions for the admin preview pages. Admins have full
     * visibility, so no ownership scoping is applied. Raw question rows are
     * returned; the frontend parses the JSON option columns.
     */
    public function questions(Exam $exam)
    {
        return response([
            'questions' => $exam->questions
        ]);
    }


    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $exam->update($request->all());
        return response([
            'message' => __('messages.exam_updated_successfully')
        ]);
    }
    public function store(Request $request)
    {
        $exam = new Exam();
        $exam->fill($request->all());
        $exam->save();
        return response([
            'message' => __('messages.exam_created_successfully')
        ]);
    }

    public function delete(Exam $exam)
    {
        $exam->delete();
        return response([
            'message' => __('messages.exam_deleted_successfully')
        ]);
    }

    public function updateStatus(UpdateStatusExamRequest $request, Exam $exam)
    {
        $exam->status = $request->status;

        if ($request->status == 'rejected') {
            $exam->rejected_reason = $request->note ?? $request->reason;
        } else {
            $exam->rejected_reason = null;
        }

        $exam->save();

        if ($exam->teacher_id) {
            Notification::create([
                'type' => 'exam_status_updated',
                'message' => __('messages.exam_status_updated_to_'.$exam->status),
                'data' => ['exam' => $exam],
                'notifiable_id' => $exam->teacher_id,
                'notifiable_type' => 'teacher',
                'read_at' => null,
                'locale' => app()->getLocale(),
            ]);
        }

        return response([
            'exam' => $exam,
            'message' => __('messages.exam_status_updated_successfully')
        ]);
    }



    public function recommend(Exam $exam)
    {
        if ($exam->recommended = 'yes') {
            $exam->recommended = 'no';
        } else {
            $exam->recommended = 'yes';
        }
        $exam->save();
        return response([
            'message' => __('messages.exam_recommended_successfully')
        ]);
    }
}
