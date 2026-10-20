<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Instructor\Concerns\InteractsWithInstructor;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupAssignment;
use App\Models\Institute;
use App\Models\Student;
use App\Models\Teacher;

class GroupController extends Controller
{
    use InteractsWithInstructor;

    public function index(Request $request)
    {
        if ($this->isInstitute()) {
            $institute = $this->getInstructor();

            $assignedGroupIds = GroupAssignment::where('institute_id', $institute->id)->pluck('group_id');

            $groups = Group::query()
                ->where(function ($query) use ($institute, $assignedGroupIds) {
                    $query->where('institute_id', $institute->id)
                          ->where('teacher_id', null);
                })
                ->orWhereIn('id', $assignedGroupIds)
                ->with(['students', 'institute', 'teacher'])
                ->withCount('students')
                ->get();

            $groups->each(function ($group) use ($assignedGroupIds) {
                $group->is_assigned = in_array($group->id, $assignedGroupIds->toArray());
            });

            return response(['groups' => $groups]);
        }

        $teacher = $this->getInstructor();
        $type = $request->get('type', 'default');

        $query = Group::query()->with(['students', 'institute', 'teacher'])->withCount('students');

        switch ($type) {
            case 'owned':
                $assignedGroupIds = GroupAssignment::query()
                    ->where('teacher_id', $teacher->id)
                    ->when($teacher->selected_institute, function ($q) use ($teacher) {
                        $q->where('institute_id', $teacher->selected_institute);
                    })
                    ->pluck('group_id');

                $query->where('teacher_id', $teacher->id)
                      ->whereNotIn('id', $assignedGroupIds);
                break;

            case 'assigned':
                $ids = GroupAssignment::query()->where('teacher_id', $teacher->id);
                if ($teacher->selected_institute) {
                    $ids->where('institute_id', $teacher->selected_institute);
                }
                $ids = $ids->pluck('group_id');
                $query->whereIn('id', $ids);
                break;

            case 'all':
                $assignedIds = GroupAssignment::query()
                    ->where('teacher_id', $teacher->id)
                    ->pluck('group_id');

                $ownedIds = Group::query()
                    ->where('teacher_id', $teacher->id)
                    ->whereNotIn('id', $assignedIds)
                    ->pluck('id');

                $allGroupIds = $ownedIds->merge($assignedIds)->unique();
                $query->whereIn('id', $allGroupIds);
                break;

            case 'default':
            default:
                if ($teacher->selected_institute) {
                    $assignedIds = GroupAssignment::query()
                        ->where('teacher_id', $teacher->id)
                        ->where('institute_id', $teacher->selected_institute)
                        ->pluck('group_id');

                    $ownedIds = Group::query()
                        ->where('teacher_id', $teacher->id)
                        ->where('institute_id', $teacher->selected_institute)
                        ->whereNotIn('id', $assignedIds)
                        ->pluck('id');

                    $allGroupIds = $ownedIds->merge($assignedIds)->unique();
                    $query->whereIn('id', $allGroupIds);
                } else {
                    // Standalone context: only the teacher's own non-institute groups.
                    $query->where('teacher_id', $teacher->id)->whereNull('institute_id');
                }
                break;
        }

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $groups = $query->get();

        $assignedGroupIds = GroupAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->pluck('group_id')
            ->toArray();

        $groups->each(function ($group) use ($assignedGroupIds) {
            $group->is_assigned = in_array($group->id, $assignedGroupIds);
        });

        return response([
            'groups' => $groups,
            'type' => $type,
            'total_count' => $groups->count()
        ]);
    }

    public function store(Request $request)
    {
        if ($this->isInstitute()) {
            $institute = $this->getInstructor();
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'integer|exists:students,id'
            ]);

            // Institutes own groups via institute_id only; teacher_id stays null at creation.
            $data['institute_id'] = $institute->id;
            $data['teacher_id'] = null;
            $group = Group::create($data);

            if ($request->has('student_ids')) {
                $group->students()->attach($data['student_ids']);
            }

            return response([
                'message' => __('messages.group_created_successfully'),
                'group' => $group->load(['students', 'teacher']),
            ], 201);
        }

        $teacher = $this->getInstructor();
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'logo' => 'nullable',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
        ]);

        // Only allow attaching students that belong to this teacher.
        if (!empty($data['student_ids'])) {
            $ownedStudentIds = $teacher->students()->pluck('students.id')->all();
            $invalidIds = array_diff($data['student_ids'], $ownedStudentIds);
            if (!empty($invalidIds)) {
                return response([
                    'message' => __('messages.student_not_found'),
                    'invalid_student_ids' => array_values($invalidIds),
                ], 422);
            }
        }

        $data['teacher_id'] = $teacher->id;
        $data['institute_id'] = $teacher->selected_institute ?? null;
        $group = Group::create($data);

        if (!empty($data['student_ids'])) {
            $group->students()->attach($data['student_ids']);
        }

        return response([
            'message' => __('messages.group_created_successfully'),
            'group' => $group->load(['students']),
        ], 201);
    }

    public function getAssignedGroups(Request $request)
    {
        if ($this->isInstitute()) {
            $institute = $this->getInstructor();
            $ids = GroupAssignment::where('institute_id', $institute->id)->pluck('group_id');
            $groups = Group::query()->with(['students', 'institute', 'teacher'])->whereIn('id', $ids);
            if ($request->search) {
                $groups->where(fn ($q) => $q->where('title', 'like', "%" . $request->search . "%"));
            }
            return response(['groups' => $groups->paginate(10)]);
        }

        $teacher = $this->getInstructor();

        $ids = GroupAssignment::query()->where('teacher_id', $teacher->id);
        if ($teacher->selected_institute) {
            $ids->where('institute_id', $teacher->selected_institute);
        }
        $ids = $ids->pluck('group_id');
        $groups = Group::query()->with(['students', 'institute', 'teacher'])->whereIn('id', $ids);
        if ($request->search) {
            $groups->where(fn ($q) => $q->where('title', 'like', "%" . $request->search . "%"));
        }
        return response(['groups' => $groups->paginate(10)]);
    }

    public function show(Group $group)
    {
        $this->authorizeAction($group);

        $instructor = $this->getInstructor();
        $isAssigned = GroupAssignment::query()
            ->where($this->ownerColumn(), $instructor->id)
            ->where('group_id', $group->id)
            ->exists();

        $group->is_assigned = $isAssigned;

        return response(['group' => $group->load(['students', 'institute', 'teacher', 'groupAssignments'])]);
    }

    public function update(Group $group, Request $request)
    {
        $this->authorizeAction($group);

        if ($this->isInstitute()) {
            $institute = $this->getInstructor();
            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'teacher_id' => 'nullable|integer|exists:teachers,id',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'integer|exists:students,id'
            ]);

            if (isset($data['teacher_id']) && !$institute->teachers()->where('teachers.id', $data['teacher_id'])->exists()) {
                return response(['message' => __('messages.teacher_not_in_institute')], 422);
            }

            $group->update($data);

            if ($request->has('student_ids')) {
                $group->students()->sync($data['student_ids']);
            }

            return response(['group' => $group->load(['students', 'teacher'])]);
        }

        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'logo' => 'nullable',
        ]);
        $group->update($data);

        if ($request->has('student_ids')) {
            $group->students()->sync($request->student_ids);
        }

        return response(['group' => $group->load(['students'])]);
    }

    public function delete(Group $group)
    {
        $this->authorizeAction($group);
        $group->delete();
        return response(['message' => __('messages.group_deleted_successfully')]);
    }

    public function addStudent(Student $student, Group $group)
    {
        $this->authorizeAction($group);

        if ($this->isInstitute()) {
            if (!$student->institutes()->where('institutes.id', $this->getInstructor()->id)->exists()) {
                return response(['message' => __('messages.student_not_found_in_institute')], 409);
            }
        }

        if ($group->students()->where('students.id', $student->id)->exists()) {
            return response(['message' => __('messages.student_already_in_group')], 409);
        }

        $group->students()->attach($student->id);
        return response(['message' => __('messages.student_added_to_group_successfully')]);
    }

    public function removeStudent(Student $student, Group $group)
    {
        $this->authorizeAction($group);
        if (!$group->students()->where('students.id', $student->id)->exists()) {
            return response(['message' => __('messages.student_not_found_in_group')], 409);
        }

        $group->students()->detach($student->id);
        return response(['message' => __('messages.student_removed_from_group_successfully')]);
    }

    private function authorizeAction(Group $group)
    {
        $instructor = $this->getInstructor();

        if ($this->isInstitute()) {
            $isOwned = $group->institute_id == $instructor->id;
            $isAssigned = GroupAssignment::where([
                'group_id' => $group->id,
                'institute_id' => $instructor->id
            ])->exists();

            if (!$isOwned && !$isAssigned) {
                abort(403, __('messages.unauthorized_group_access'));
            }

            return;
        }

        $isOwned = $group->teacher_id == $instructor->id;
        $isAssigned = GroupAssignment::where([
            'group_id' => $group->id,
            'teacher_id' => $instructor->id,
        ])->exists();

        if (!$isOwned && !$isAssigned) {
            abort(403, __('messages.unauthorized_group_access'));
        }
    }

    public function assign(Group $group, Request $request)
    {
        $this->authorizeAction($group);

        if ($this->isInstitute()) {
            $institute = $this->getInstructor();

            if (!$institute->teachers()->where('teachers.id', $request->teacher_id)->exists()) {
                return response(['message' => __('messages.teacher_not_in_institute')], 422);
            }

            GroupAssignment::updateOrCreate([
                'group_id' => $group->id,
                'institute_id' => $institute->id,
                'teacher_id' => $request->teacher_id,
            ]);
            return response(['message' => __('messages.group_assigned_successfully')]);
        }

        GroupAssignment::updateOrCreate([
            'group_id' => $group->id,
            'institute_id' => $request->institute_id,
            'teacher_id' => $this->getInstructor()->id,
        ]);
        return response(['message' => __('messages.group_assigned_successfully')]);
    }

    public function unassign(Group $group, Request $request)
    {
        $this->authorizeAction($group);

        if ($this->isInstitute()) {
            GroupAssignment::where([
                'group_id' => $group->id,
                'institute_id' => $this->getInstructor()->id,
                'teacher_id' => $request->teacher_id,
            ])->delete();
            return response(['message' => __('messages.group_unassigned_successfully')]);
        }

        GroupAssignment::where([
            'group_id' => $group->id,
            'institute_id' => $request->institute_id,
            'teacher_id' => $this->getInstructor()->id,
        ])->delete();
        return response(['message' => __('messages.group_unassigned_successfully')]);
    }
}
