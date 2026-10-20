<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Models\Category;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Institute;
use App\Models\Label;
use App\Models\Teacher;
use Illuminate\Http\Request;

trait DiscoverLogic
{
    public function discover()
    {
        $teacher = auth('teacher')->user();
        $institute = auth('institute')->user();

        $categories = Category::withCount('exams')
            ->orderBy('exams_count', 'desc')
            ->get();

        $top_ayhoots = Exam::query()
            ->published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount(['games as plays_count', 'questions'])
            ->when($teacher, fn($q) => $q->where(fn($sub) => $sub->whereNull('teacher_id')->orWhere('teacher_id', '!=', $teacher->id)))
            ->when($institute, fn($q) => $q->where(fn($sub) => $sub->whereNull('institute_id')->orWhere('institute_id', '!=', $institute->id)))
            ->orderBy('plays_count', 'desc')
            ->limit(6)
            ->get();

        $labels = Label::with([
            'exams' => fn($q) => $q->where('status', 'published')
                ->with(['teacher', 'institute', 'category'])
                ->withCount('games as plays_count', 'questions')
                ->limit(10),
            'courses' => fn($q) => $q->with(['teacher', 'institute', 'category'])
                ->withCount('students')
                ->limit(10),
            'teachers' => fn($q) => $q->with(['plan', 'institutes'])
                ->withCount('courses', 'students')
                ->limit(10),
            'institutes' => fn($q) => $q->with(['plan'])
                ->withCount('teachers', 'courses', 'students')
                ->limit(10)
        ])->get();

        $recommended_exams = Exam::published()
            ->whereHas('labels', function ($query) {
                $query->where('title', 'like', "%recommended%")->orWhere('description', 'like', "%recommended%");
            })
            ->when($teacher, fn($q) => $q->where(fn($sub) => $sub->whereNull('teacher_id')->orWhere('teacher_id', '!=', $teacher->id)))
            ->when($institute, fn($q) => $q->where(fn($sub) => $sub->whereNull('institute_id')->orWhere('institute_id', '!=', $institute->id)))
            ->with(['category', 'teacher', 'institute'])
            ->withCount('students')
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'categories' => $categories,
            'top_ayhoots' => $top_ayhoots,
            'recommended_exams' => $recommended_exams,
            'labels' => $labels,
        ]);
    }


    public function getAllLabels(Request $request)
    {
        $labels = Label::with([
            'exams' => fn($q) => $q->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions')->limit(10),
            'courses' => fn($q) => $q->with(['teacher', 'institute', 'category'])->withCount('students')->limit(10),
            'teachers' => fn($q) => $q->with(['plan', 'institutes'])->withCount('courses', 'students')->limit(10),
            'institutes' => fn($q) => $q->with(['plan'])->withCount('teachers', 'courses', 'students')->limit(10)
        ])->get();
        return response(['labels' => $labels]);
    }

    public function getAllCourses(Request $request)
    {
        $courses = Course::query()->with(['teacher', 'institute', 'category'])->withCount('students')->orderBy('id', 'desc');
        if ($request->has('search')) {
            $courses->where('title', 'like', "%{$request->search}%");
        }
        if ($request->has('plays')) {
            $courses->where('students_count', '>=', $request->plays);
        }
        return response(['courses' => $courses->paginate(10)]);
    }

    public function getAllExams(Request $request)
    {
        $exams = Exam::query()->published()->with(['teacher', 'institute', 'category'])->withCount('games as plays_count')->orderBy('plays_count', 'desc');
        if ($request->has('search')) {
            $exams->where('title', 'like', "%{$request->search}%");
        }
        if ($request->has('search_description')) {
            $exams->where('description', 'like', "%{$request->search_description}%");
        }
        if ($request->has('plays')) {
            $exams->where('plays_count', '>=', 'plays');
        }
        return response(['exams' => $exams->paginate(10)]);
    }

    public function getAllTeachers(Request $request)
    {
        $teachers = Teacher::query()->with(['plan', 'institutes'])->withCount('courses', 'students')->orderBy('id', 'desc');
        if ($request->has('search')) {
            $teachers->where('first_name', 'like', "%{$request->search}%")->orWhere('last_name', 'like', "%{$request->search}%");
        }
        return response(['teachers' => $teachers->paginate(10)]);
    }

    public function getAllGroups(Request $request)
    {
        $groups = Group::query()->with(['teacher', 'institute', 'category'])->withCount('students')->orderBy('id', 'desc');
        if ($request->has('search')) {
            $groups->where('name', 'like', "%{$request->search}%");
        }
        return response(['groups' => $groups->paginate(10)]);
    }



    public function label(Request $request, Label $label)
    {
        $perPage = $request->get('per_page', 10);
        $exams = $label->exams()->where('status', 'published')->with(['teacher', 'institute', 'category'])->withCount(['games as plays_count', 'questions'])->paginate($perPage, ['*'], 'exams_page');
        $courses = $label->courses()->with(['teacher', 'institute', 'category'])->withCount(['students'])->paginate($perPage, ['*'], 'courses_page');
        $teachers = $label->teachers()->with(['plan', 'institutes'])->withCount(['courses', 'students'])->paginate($perPage, ['*'], 'teachers_page');
        $institutes = $label->institutes()->with(['plan'])->withCount(['teachers', 'courses', 'students'])->paginate($perPage, ['*'], 'institutes_page');
        return response(['label' => $label, 'exams' => $exams, 'courses' => $courses, 'teachers' => $teachers, 'institutes' => $institutes]);
    }

    public function teacher(Teacher $teacher)
    {
        $teacher->load(['institutes.plan', 'plan']);

        $exams = Exam::where('teacher_id', $teacher->id)
            ->whereNull('archive')
            ->published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount(['questions', 'games as plays_count'])
            ->get();

        $exams->transform(function ($exam) {
            $gamesCount = $exam->plays_count ?? 0;
            $answersCount = \App\Models\Answer::where('exam_id', $exam->id)->count();
            $realPlays = max($gamesCount, $answersCount);
            $exam->plays_count = $realPlays;
            $exam->students_count = $realPlays;
            return $exam;
        });

        $courses = Course::where('teacher_id', $teacher->id)
            ->whereNull('archive')
            ->with(['category', 'teacher', 'institute'])
            ->withCount(['students', 'exams'])
            ->get();

        $teacher->setRelation('exams', $exams);
        $teacher->setRelation('courses', $courses);

        $playsCount = $exams->sum('plays_count');
        $studentsCount = $teacher->students()->count();

        return response([
            'teacher' => $teacher,
            'plays_count' => $playsCount,
            'students_count' => $studentsCount,
        ]);
    }

    public function institute(Institute $institute)
    {
        $institute->load(['plan', 'teachers.plan']);

        $exams = Exam::where('institute_id', $institute->id)
            ->whereNull('archive')
            ->published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount(['questions', 'games as plays_count'])
            ->get();

        $exams->transform(function ($exam) {
            $gamesCount = $exam->plays_count ?? 0;
            $answersCount = \App\Models\Answer::where('exam_id', $exam->id)->count();
            $realPlays = max($gamesCount, $answersCount);
            $exam->plays_count = $realPlays;
            $exam->students_count = $realPlays;
            return $exam;
        });

        $courses = Course::where('institute_id', $institute->id)
            ->whereNull('archive')
            ->with(['category', 'teacher', 'institute'])
            ->withCount(['students', 'exams'])
            ->get();

        $institute->setRelation('exams', $exams);
        $institute->setRelation('courses', $courses);

        $playsCount = $exams->sum('plays_count');
        $studentsCount = $institute->students()->count();

        return response([
            'institute' => $institute,
            'plays_count' => $playsCount,
            'students_count' => $studentsCount,
        ]);
    }

    public function course(Course $course)
    {
        $course->load(['teacher.plan', 'institute.plan', 'category', 'labels']);
        return response(['course' => $course]);
    }

    public function exam(Exam $exam)
    {
        $exam->load(['teacher.plan', 'institute.plan', 'category', 'labels']);
        return response(['exam' => $exam]);
    }

    public function group(Group $group)
    {
        $group->loadCount('students')->load(['teacher.plan', 'institute.plan']);
        return response(['group' => $group]);
    }

    public function category(Category $category)
    {
        $exams = $category->exams()->where('status', 'published')->with(['teacher', 'institute'])->withCount('games as plays_count', 'questions')->paginate(10, ['*'], 'exams_page');
        $courses = $category->courses()->with(['teacher', 'institute'])->withCount('students')->paginate(10, ['*'], 'courses_page');

        return response([
            'category' => $category->loadCount(['exams', 'courses']),
            'exams' => $exams,
            'courses' => $courses
        ]);
    }
}
