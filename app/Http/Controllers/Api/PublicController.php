<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\City;
use App\Models\Course;
use App\Models\Exam;
use App\Models\HeroSlide;
use App\Models\Institute;
use App\Models\Label;
use App\Models\News;
use App\Models\Plan;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicController extends Controller
{
    public function home()
    {
        $plans = Plan::all();
        $categories = Category::all();

        $heroSlides = HeroSlide::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $topTeachers = Teacher::withCount('students', 'courses', 'exams')
            ->whereNull('code')
            ->orderByDesc('exams_count')
            ->orderByDesc('students_count')
            ->take(4)
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'type' => 'teacher',
                    'name' => $teacher->name,
                    'image' => $teacher->avatar,
                    'tutees' => $teacher->students_count,
                    'ayhoots' => $teacher->exams_count,
                    'courses' => $teacher->courses_count,
                ];
            });

        $topInstitutes = Institute::withCount('students', 'courses', 'exams')
            ->whereNull('code')
            ->orderByDesc('exams_count')
            ->orderByDesc('students_count')
            ->take(4)
            ->get()
            ->map(function ($institute) {
                return [
                    'id' => $institute->id,
                    'type' => 'institute',
                    'name' => $institute->name,
                    'image' => $institute->avatar,
                    'tutees' => $institute->students_count,
                    'ayhoots' => $institute->exams_count,
                    'courses' => $institute->courses_count,
                ];
            });

        $creators = $topTeachers->concat($topInstitutes)
            ->sort(function ($a, $b) {
                if ($b['ayhoots'] !== $a['ayhoots']) {
                    return $b['ayhoots'] <=> $a['ayhoots'];
                }
                return $b['tutees'] <=> $a['tutees'];
            })
            ->values()
            ->take(4);

        return response([
            'plans' => $plans,
            'categories' => $categories,
            'creators' => $creators,
            'hero_slides' => $heroSlides,
        ]);
    }

    public function heroSlides()
    {
        $slides = HeroSlide::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response([
            'slides' => $slides,
        ]);
    }


    public function plans()
    {
        $plans = Plan::all();
        return response([
            'plans' => $plans
        ]);
    }


    public function setlocale(Request $request)
    {
        $locale = $request->input('locale');

        // Validate locale
        $supportedLocales = \App\Services\LocaleService::getSupportedLocales();
        if (!in_array($locale, $supportedLocales)) {
            return response([
                'message' => 'Unsupported locale',
                'supported_locales' => $supportedLocales
            ], 400);
        }

        // Set the locale
        app()->setLocale($locale);

        return response([
            'message' => __('messages.locale_set_successfully', ['locale' => $locale]),
            'locale' => $locale,
            'filtering_enabled' => \App\Services\LocaleService::isLocaleFilteringEnabled()
        ]);
    }

    public function getlocale(Request $request)
    {
        $locale = $request->input('locale');
        return response([
            'locale' => $locale,
        ]);
    }

    public function news(Request $request)
    {
        $news = News::query()->where('status', 'published')->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $news->where('title', 'like', "%{$request->search}%");
        }
        return response([
            'news' => $news->paginate(10)
        ]);
    }

    public function showNews(News $news)
    {
        if ($news->status !== 'published') {
            return response(['message' => __('messages.news_not_found')], 409);
        }
        return response([
            'news' => $news
        ]);
    }

    public function discover()
    {
        $categories = Category::withCount('exams')->orderBy('exams_count', 'desc')->get();

        $examQuery = Exam::published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions');

        if (\App\Services\LocaleService::isLocaleFilteringEnabled()) {
            $examQuery->where('locale', \App\Services\LocaleService::getCurrentLocale());
        }

        $top_ayhoots = $examQuery->orderBy('plays_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($exam) {
                $exam->students_count = $exam->students_count; // Trigger accessor
                return array_merge($exam->toArray(), [
                    'creator' => $exam->teacher ?? $exam->institute,
                    'creator_type' => $exam->teacher ? 'teacher' : 'institute'
                ]);
            });

        $courseQuery = Course::with(['category', 'teacher', 'institute'])
            ->withCount('students');

        if (\App\Services\LocaleService::isLocaleFilteringEnabled()) {
            $courseQuery->where('locale', \App\Services\LocaleService::getCurrentLocale());
        }

        $verified_courses = $courseQuery->orderBy('id', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($course) {
                return array_merge($course->toArray(), [
                    'creator' => $course->teacher ?? $course->institute,
                    'creator_type' => $course->teacher ? 'teacher' : 'institute'
                ]);
            });

        $labels = Label::with([
            'exams' => function ($q) {
                $q->where('status', 'published')->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions');
                if (\App\Services\LocaleService::isLocaleFilteringEnabled()) {
                    $q->where('locale', \App\Services\LocaleService::getCurrentLocale());
                }
                $q->limit(10);
            },
            'courses' => function ($q) {
                $q->with(['teacher', 'institute', 'category'])->withCount('students');
                if (\App\Services\LocaleService::isLocaleFilteringEnabled()) {
                    $q->where('locale', \App\Services\LocaleService::getCurrentLocale());
                }
                $q->limit(10);
            },
            'teachers' => fn($q) => $q->with(['plan', 'institutes'])->withCount('courses', 'students')->limit(10),
            'institutes' => fn($q) => $q->with(['plan'])->withCount('teachers', 'courses', 'students')->limit(10)
        ])->get();
        
        // Transform exams in labels to include students_count
        $labels->each(function ($label) {
            $label->exams->transform(function ($exam) {
                $exam->students_count = $exam->students_count;
                return $exam;
            });
        });

        return response([
            'categories' => $categories,
            'top_ayhoots' => $top_ayhoots,
            'verified_courses' => $verified_courses,
            'labels' => $labels,
            'user_filtered' => false
        ]);
    }

    public function teacher(Request $request, Teacher $teacher)
    {
        if (!$teacher) {
            return response(['message' => __('messages.teacher_not_found')], 409);
        }

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

    public function institute(Request $request, Institute $institute)
    {
        if (!$institute) {
            return response(['message' => __('messages.institute_not_found')], 409);
        }

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

    public function category(Request $request, Category $category)
    {
        // Get exams for this specific category
        $top_ayhoots = Exam::published()
            ->where('category_id', $category->id)
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->orderBy('plays_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($exam) {
                return array_merge($exam->toArray(), [
                    'creator' => $exam->teacher ?? $exam->institute,
                    'creator_type' => $exam->teacher ? 'teacher' : 'institute'
                ]);
            });

        // Get courses for this specific category
        $verified_courses = Course::where('category_id', $category->id)
            ->with(['category', 'teacher', 'institute'])
            ->withCount('students')
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($course) {
                return array_merge($course->toArray(), [
                    'creator' => $course->teacher ?? $course->institute,
                    'creator_type' => $course->teacher ? 'teacher' : 'institute'
                ]);
            });

        // Filter labels to only show content from this category
        $labels = Label::with([
            'exams' => fn($q) => $q->where('status', 'published')
                ->where('category_id', $category->id)
                ->with(['teacher', 'institute', 'category'])
                ->withCount('games as plays_count', 'questions')
                ->limit(10),
            'courses' => fn($q) => $q->where('category_id', $category->id)
                ->with(['teacher', 'institute', 'category'])
                ->withCount('students')
                ->limit(10),
            'teachers' => fn($q) => $q->whereHas('exams', function ($subQ) use ($category) {
                $subQ->where('category_id', $category->id);
            })
                ->orWhereHas('courses', function ($subQ) use ($category) {
                    $subQ->where('category_id', $category->id);
                })
                ->with(['plan', 'institutes'])
                ->withCount('courses', 'students')
                ->limit(10),
            'institutes' => fn($q) => $q->whereHas('exams', function ($subQ) use ($category) {
                $subQ->where('category_id', $category->id);
            })
                ->orWhereHas('courses', function ($subQ) use ($category) {
                    $subQ->where('category_id', $category->id);
                })
                ->with(['plan'])
                ->withCount('teachers', 'courses', 'students')
                ->limit(10)
        ])->get();

        // Add pagination for recent_ayhoots
        $recent_ayhoots = Exam::published()
            ->where('category_id', $category->id)
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->through(function ($exam) {
                return array_merge($exam->toArray(), [
                    'creator' => $exam->teacher ?? $exam->institute,
                    'creator_type' => $exam->teacher ? 'teacher' : 'institute'
                ]);
            });

        return response([
            'top_ayhoots' => $top_ayhoots,
            'verified_courses' => $verified_courses,
            'recent_ayhoots' => $recent_ayhoots,
            'labels' => $labels,
            'category' => $category,
            'user_filtered' => true // Indicates this shows category-filtered content
        ]);
    }

    public function label(Request $request, Label  $label)
    {
        if (!$label) {
            return response(['message' => __('messages.label_not_found')], 409);
        }
        return response([
            'label' => $label
        ]);
    }

    public function exam(Request $request, Exam $exam)
    {
        if (!$exam) {
            return response(['message' => __('messages.exam_not_found')], 409);
        }
        
        $exam->loadCount('games as plays_count', 'questions');
        // Trigger the students_count accessor
        $exam->students_count = $exam->students_count;
        
        return response([
            'exam' => $exam
        ]);
    }
    public function labels(Request $request)
    {
        $labels = Label::with([
            'exams' => fn($q) => $q->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions')->limit(10),
            'courses' => fn($q) => $q->with(['teacher', 'institute', 'category'])->withCount('students')->limit(10),
            'teachers' => fn($q) => $q->with(['plan', 'institutes'])->withCount('courses', 'students')->limit(10),
            'institutes' => fn($q) => $q->with(['plan'])->withCount('teachers', 'courses', 'students')->limit(10)
        ])->get();
        return response([
            'labels' => $labels
        ]);
    }
    public function teachers(Request $request)
    {
        $teachers = Teacher::withCount('courses', 'exams')->orderBy('created_at', 'desc')->paginate(10);
        return response([
            'teachers' => $teachers
        ]);
    }
    public function institutes(Request $request)
    {
        $institutes = Institute::withCount('courses', 'exams')->orderBy('created_at', 'desc')->paginate(10);
        return response([
            'institutes' => $institutes
        ]);
    }
    public function categories(Request $request)
    {
        $categories = Category::withCount('exams')->orderBy('exams_count', 'desc')->paginate(10);
        return response([
            'categories' => $categories
        ]);
    }

    public function exams(Request $request)
    {
        $exams = Exam::published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->through(function ($exam) {
                $exam->students_count = $exam->students_count; // Trigger accessor
                return array_merge($exam->toArray(), [
                    'creator' => $exam->teacher ?? $exam->institute,
                    'creator_type' => $exam->teacher ? 'teacher' : 'institute'
                ]);
            });

        return response([
            'exams' => $exams,
            'user_filtered' => false // Shows all content (public view)
        ]);
    }


    public function topExams(Request $request)
    {
        $ayhoots = Exam::published()
            ->with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->orderBy('plays_count', 'desc')
            ->paginate(10);
            
        // Add students_count using the accessor for each exam
        $ayhoots->getCollection()->transform(function ($exam) {
            $exam->students_count = $exam->students_count;
            return $exam;
        });
        
        return response([
            'ayhoots' => $ayhoots
        ]);
    }

    public function recommendedExams(Request $request)
    {
        $recommended_exams = Exam::published()->whereHas('labels', function ($query) {
            $query->where('title', 'like', "%recommended%")->orWhere('description', 'like', "%recommended%");
        })->with(['category', 'teacher', 'institute'])
        ->withCount('games as plays_count', 'questions')
        ->orderBy('id', 'desc')
        ->paginate(10);
        
        // Add students_count using the accessor for each exam
        $recommended_exams->getCollection()->transform(function ($exam) {
            $exam->students_count = $exam->students_count;
            return $exam;
        });
        
        return response([
            'recommended_exams' => $recommended_exams
        ]);
    }


    public function courses(Request $request)
    {
        $courses = Course::whereNull('archive')
            ->with(['category', 'teacher', 'institute'])
            ->withCount('students')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->through(function ($course) {
                return array_merge($course->toArray(), [
                    'creator' => $course->teacher ?? $course->institute,
                    'creator_type' => $course->teacher ? 'teacher' : 'institute'
                ]);
            });

        return response([
            'courses' => $courses,
            'user_filtered' => false // Shows all content (public view)
        ]);
    }


    public function course(Request $request, Course $course)
    {
        if (!$course) {
            return response(['message' => __('messages.course_not_found')], 409);
        }
        return response([
            'course' => $course
        ]);
    }
    public function recommendedCourses(Request $request)
    {
        $recommended_courses = Course::whereHas('labels', function ($query) {
            $query->where('title', 'like', "%recommended%")->orWhere('description', 'like', "%recommended%");
        })->with(['category', 'teacher', 'institute'])->withCount('students')->orderBy('id', 'desc')->paginate(10);
        return response([
            'recommended_courses' => $recommended_courses
        ]);
    }
    public function topCourses(Request $request)
    {
        $top_courses = Course::whereHas('labels', function ($query) {
            $query->where('title', 'like', "%top%")->orWhere('description', 'like', "%top%");
        })->with(['category', 'teacher', 'institute'])->withCount('students')->orderBy('id', 'desc')->paginate(10);
        return response([
            'top_courses' => $top_courses
        ]);
    }


    public function creators(Request $request)
    {
        $search = $request->query('search', '');
        $type = $request->query('type', 'all'); // 'all', 'teacher', 'institute'
        $perPage = $request->query('per_page', 15);

        // Validate per_page to prevent excessive values
        $perPage = min(max($perPage, 1), 100);

        $teachersQuery = Teacher::withCount('students', 'courses', 'exams')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            });

        $institutesQuery = Institute::withCount('students', 'courses', 'exams')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            });

        // Get counts for pagination
        $teacherCount = $type !== 'institute' ? $teachersQuery->count() : 0;
        $instituteCount = $type !== 'teacher' ? $institutesQuery->count() : 0;
        $totalCount = $teacherCount + $instituteCount;

        // Apply type filter and get data
        if ($type == 'teacher') {
            $teachers = $teachersQuery->orderByDesc('created_at')->get();
            $institutes = collect();
        } elseif ($type == 'institute') {
            $teachers = collect();
            $institutes = $institutesQuery->orderByDesc('created_at')->get();
        } else {
            $teachers = $teachersQuery->orderByDesc('created_at')->get();
            $institutes = $institutesQuery->orderByDesc('created_at')->get();
        }

        // Transform the data
        $topTeachers = $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'type' => 'teacher',
                'name' => $teacher->name,
                'image' => $teacher->avatar,
                'tutees' => $teacher->students_count,
                'ayhoots' => $teacher->exams_count,
                'courses' => $teacher->courses_count,
            ];
        });

        $topInstitutes = $institutes->map(function ($institute) {
            return [
                'id' => $institute->id,
                'type' => 'institute',
                'name' => $institute->name,
                'image' => $institute->avatar,
                'tutees' => $institute->students_count,
                'ayhoots' => $institute->exams_count,
                'courses' => $institute->courses_count,
            ];
        });

        // Combine and shuffle (if no search is applied)
        $creators = $topTeachers->concat($topInstitutes);

        if (empty($search)) {
            $creators = $creators->shuffle();
        }

        // Manual pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $creators->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedCreators = new LengthAwarePaginator(
            $currentItems,
            $creators->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        return response([
            'creators' => $paginatedCreators->items(),
            'pagination' => [
                'current_page' => $paginatedCreators->currentPage(),
                'last_page' => $paginatedCreators->lastPage(),
                'per_page' => $paginatedCreators->perPage(),
                'total' => $paginatedCreators->total(),
                'from' => $paginatedCreators->firstItem(),
                'to' => $paginatedCreators->lastItem(),
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
            ]
        ]);
    }

    /**
     * Get banners for a specific user type and city
     */
    public function getBanners(Request $request)
    {
        $userType = $request->query('user_type', 'student');
        $cityId = $request->query('city_id');

        // If city_id is not passed, attempt detection from authenticated user
        if (!$cityId && auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $cityId = $user?->city_id;
        }

        $banners = Banner::where('user_type', $userType)
            ->where('is_active', true)
            ->where(function ($query) use ($cityId) {
                // Global banners configured for "All Cities" (city_id is null) are always included
                $query->whereNull('city_id');
                // If a specific city is targeted or known, include banners for that city too
                if ($cityId) {
                    $query->orWhere('city_id', $cityId);
                }
            })
            ->orderBy('display_order')
            ->with('city')
            ->get();

        return response(['banners' => $banners]);
    }

    /**
     * Get all cities for signup form
     */
    public function getCities()
    {
        $cities = City::orderBy('name_en')->get();
        return response(['cities' => $cities]);
    }
}
