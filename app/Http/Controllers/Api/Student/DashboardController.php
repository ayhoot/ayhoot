<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\EnrollCoursesRequest;
use App\Http\Requests\Teacher\CardRequest;
use App\Http\Requests\Teacher\UpdateProfileRequest;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Institute;
use App\Models\Invoice;
use App\Models\Label;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Transaction;
use App\Service\Zarinpal;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Student::with('courses')->find(auth('student')->user()->id);

        if (!$student) {
            return response(['message' => __('messages.student_not_found')], 409);
        }

        // CORRECTED QUERY
        // Get the IDs of the latest exams the student has answered.
        $attended_exam_ids = $student->answers()
            ->select('exam_id')
            ->groupBy('exam_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(5)
            ->pluck('exam_id');

        // Fetch the full exam models for those IDs
        $latest_exams = Exam::whereIn('id', $attended_exam_ids)
            ->with('teacher:id,username')
            ->withCount('questions', 'students')
            ->get();

        // Fetch the latest completed reports
        $latest_reports = $student->answers()
            ->where('status', 'completed')
            ->with(['exam:id,title', 'game:id,title,end_at'])
            ->latest('completed_at')
            ->take(5)
            ->get();

        $student_data = [
            'total_courses' => $student->courses->count(),
            'total_exams_attended' => $student->answers()->distinct('exam_id')->count(),
            'student' => $student,
            'latest_courses' => $student->courses()->latest()->take(3)->get(),
            'latest_exams' => $latest_exams,
            'latest_reports' => $latest_reports,
            'student'=>$student,
        ];

        return response([
            'dashboard' => [
                'student_data' => $student_data
            ],
            'student_data' => $student_data
        ]);
    }

    public function enrollCourses(EnrollCoursesRequest $request)
    {

        $student = Student::find(auth('student')->user()->id);

        $student->courses()->syncWithoutDetaching($request->course_ids);

        return response([
            'message' => __('messages.courses_enrolled_successfully'),
            'courses' => $student->courses,
        ]);
    }

     public function saveCard(CardRequest $request)
    {
        $student = Student::find(auth('student')->user()->id);
        $student->card = $request->card;
        $student->save();
        $student->refresh();
        return response([
            'message' => __('messages.card_saved_successfully'),
            'student' => $student,
            'user' => $student,
            'teacher' => $student,
        ]);
    }

    public function buyExam(Request $request, Exam $exam)
    {
        $student = Student::find(auth('student')->user()->id);

        if ($student->hasPurchasedExam($exam->id)) {
            return response(['message' => __('messages.exam_already_purchased')], 400);
        }

        $invoice = Invoice::create([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'invoice_number' => uniqid('inv-'),
            'amount' => $exam->price,
            'status' => 'pending',
            'description' => "Buy Exam for {$exam->price} for {$exam->title}.",
            'currency' => config('zarinpal.currency', 'IRT'),
            'meta' => ['discount' => 0]
        ]);
        $zarinpal = new Zarinpal();
        $result = $zarinpal->request(
            $exam->price,
            "Invoice: " . $invoice->invoice_number,
            $student->email,
            env('APP_URL') . '/api/student/payment/verify-buy-exam'
        );
        if ($result['success']) {
            Transaction::create([
                'invoice_id' => $invoice->id,
                'gateway' => 'zarinpal',
                'transaction_id' => $result['authority'],
                'amount' => $invoice->amount,
                'status' => 'pending'
            ]);
            return response(['payment_url' => $result['start_pay_url']]);
        }
    }

    public function discover()
    {
        $categories = Category::withCount('exams')->orderBy('exams_count', 'desc')->get();
        
        $top_ayhoots = Exam::with(['category', 'teacher', 'institute'])
            ->withCount('games as plays_count', 'questions')
            ->orderBy('plays_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($exam) {
                $exam->students_count = $exam->students_count; // Trigger accessor
                return $exam;
            });
            
        $labels = Label::with(['exams' => function ($query) {
            $query->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions')->limit(10);
        }, 'courses' => function ($query) {
            $query->with(['teacher', 'institute', 'category'])->withCount('students')->limit(10);
        }, 'teachers' => function ($query) {
            $query->with(['plan', 'institutes'])->withCount('courses', 'students')->limit(10);
        }, 'institutes' => function ($query) {
            $query->with(['plan'])->withCount('teachers', 'courses', 'students')->limit(10);
        }])->get();
        
        // Transform exams in labels to include students_count
        $labels->each(function ($label) {
            $label->exams->transform(function ($exam) {
                $exam->students_count = $exam->students_count;
                return $exam;
            });
        });
        
        $recommended_exams = Exam::whereHas('labels', function ($query) {
            $query->where('title', 'like', "%recommended%")->orWhere('description', 'like', "%recommended%");
        })->with(['category', 'teacher', 'institute'])
        ->withCount('games as plays_count', 'questions')
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($exam) {
            $exam->students_count = $exam->students_count;
            return $exam;
        });

        return response(['categories' => $categories, 'top_ayhoots' => $top_ayhoots, 'labels' => $labels, 'recommended_exams' => $recommended_exams]);
    }


    public function getExams(Request $request)
    {
        $student = Student::find(auth('student')->user()->id);
        $exams = $student->exams()->with(['teacher', 'institute', 'category'])->withCount('games as plays_count', 'questions')->orderBy('plays_count', 'desc');
        if ($request->search) {
            $exams = $exams->where('title', 'like', "%" . $request->search . "%");
        }
        if ($request->status) {
            $exams = $exams->where('status', $request->status);
        }
        
        $paginatedExams = $exams->paginate(10);
        
        // Add students_count using the accessor for each exam
        $paginatedExams->getCollection()->transform(function ($exam) {
            $exam->students_count = $exam->students_count; // This triggers the accessor
            return $exam;
        });
        
        return response(['exams' => $paginatedExams]);
    }

    public function getCourses(Request $request)
    {
        $student = Student::find(auth('student')->user()->id);
        $courses = $student->courses()->with(['teacher', 'institute', 'category'])->withCount('students')->orderBy('id', 'desc');
        if ($request->search) {
            $courses = $courses->where('title', 'like', "%" . $request->search . "%");
        }
        if ($request->plays) {
            $courses = $courses->where('students_count', '>=', $request->plays);
        }
        return response(['courses' => $courses->paginate(10)]);
    }

    public function getReports(Request $request)
    {
        $student = auth('student')->user();
        if (!$student) {
            return response(['answers' => ['data' => [], 'total' => 0]], 200);
        }
        $answers = $student->answers()
            ->with(['game.exam.category', 'exam.category'])
            ->latest('id');

        return response(['answers' => $answers->paginate(10)]);
    }

    public function getReport(Answer $report)
    {
        return [
            'report_id' => $report->id,
            'game_id' => $report->game->id,
            'game_title' => $report->game->title,
            'exam_title' => $report->whenLoaded('game', fn() => $report->game->exam->title),
            'host' => $report->whenLoaded('game', fn() => optional($report->game->teacher)->name ?? optional($report->game->institute)->name),
            'completed_date' => $report->completed_at->toFormattedDateString(), // e.g., "Jul 29, 2024"
            'score' => $report->score,
            'correct_answers' => $report->correct_answers_count,
            'total_questions' => $report->total_questions_count,
            'accuracy_percentage' => $report->total_questions_count > 0
                ? round(($report->correct_answers_count / $report->total_questions_count) * 100)
                : 0,
        ];
    }

    public function getCourse(Course $course)
    {
        $course->load(['exams', 'labels', 'students.count', 'teacher', 'institute', 'category']);
        
        $student = auth('student')->user();
        $hasAccess = false;
        $needsPurchase = false;
        $purchased = 'no';

        if ($course->monetize == 'free') {
            $hasAccess = true;
            $purchased = 'yes';
        } elseif ($student) {
            // Use the unified access control method
            $hasAccess = $course->hasAccessFor($student);
            
            if ($hasAccess) {
                $purchased = 'yes';
            } else {
                $needsPurchase = true;
            }
        } else {
            $needsPurchase = true;
        }

        // Add purchase status to course object
        $course->purchased = $purchased;
        
        return response([
            'course' => $course,
            'has_access' => $hasAccess,
            'needs_purchase' => $needsPurchase,
            'is_free' => $course->monetize == 'free'
        ]);
    }

    public function verifyBuyExam(Request $request)
    {
        $request->validate([
            'authority' => 'required|string',
            'amount' => 'required|numeric',
            'status' => 'required|string',
        ]);
        $transaction = Transaction::where('transaction_id', $request->authority)->first();
        $invoice = Invoice::where('id', $transaction->invoice_id)->first();
        if (!$transaction) {
            return response(['message' => 'Invalid transaction id'], 400);
        }
        if (!$invoice) {
            return response(['message' => 'Invalid invoice number'], 400);
        }
        if ($invoice->status != 'pending') {
            return response(['message' => 'Invoice not pending'], 400);
        }
        if ($transaction->status != 'pending') {
            return response(['message' => 'Transaction not pending'], 400);
        }
        if ($invoice->amount != $request->amount) {
            return response(['message' => 'Amount mismatch'], 400);
        }
        if ($invoice->student_id != $transaction->invoice->student_id) {
            return response(['message' => 'Student mismatch'], 400);
        }
        if ($invoice->exam_id != $transaction->invoice->exam_id) {
            return response(['message' => 'Exam mismatch'], 400);
        }
        $invoice->status = 'paid';
        $invoice->save();
        $transaction->status = 'successful';
        $transaction->save();
        return response(['message' => __('messages.exam_purchased')]);
    }
    public function notifications(Request $request)
    {
        $student = Student::find(auth('student')->user()->id);
        $notifications = $student->notifications()->recent()->get();
        return response(['notifications' => $notifications]);
    }
    public function markAllNotificationsAsRead(Request $request)
    {
        $student = Student::find(auth('student')->user()->id);
        $student->markAllNotificationsAsRead();
        return response(['message' => __('messages.notifications_read_successfully')]);
    }
    public function markNotificationAsRead(Request $request, Notification $notification)
    {
        $notification->markAsRead();
        return response(['message' => __('messages.notification_read_successfully')]);
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        $student = Student::find(auth('student')->id());
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $student->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file=$file->move(public_path('uploads/student_avatars'), $filename);
            $data['avatar'] = 'student_avatars/'.$filename;
        }
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = 'cover_' . $student->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file=$file->move(public_path('uploads/student_covers'), $filename);
            $data['cover'] = 'student_covers/'.$filename;
        }
        $student->update($data);
        $student->refresh()->load('institutes');
        return response(['message' => __('messages.profile_updated_successfully'), 'student' => $student]);
    }

    public function groups(Request $request){
        $student=Student::find(auth('student')->user()->id);
        $groups=$student->groups();
        return response([
            'groups'=>$groups->paginate(10)
        ]);
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


    public function group(Group $group){
        return response([
            'group'=>$group
        ]);
    }

    public function purchasedExams(){
        $student = auth('student')->user();

$purchasedExamIds = collect()

    ->merge(
        $student->exams()->pluck('exams.id')
    )

    ->merge(
        \App\Models\ExamPurchase::where('purchaser_id', $student->id)
            ->where('purchaser_type', 'student')
            ->where('payment_status', 'completed')
            ->pluck('exam_id')
    )

    ->unique();

$purchasedExams = \App\Models\Exam::whereIn('id', $purchasedExamIds)->paginate(10);
return response([
    'exam'=>$purchasedExams
]);

    }

    /* ---- Wallet ---- */

    public function invoices()
    {
        $student = Student::find(auth('student')->user()->id);
        $invoices = Invoice::where('student_id', $student->id)
            ->orderBy('id', 'desc')
            ->with(['transactions', 'teacher', 'student', 'exam', 'institute', 'game', 'course'])
            ->paginate(10);
        return response(['invoices' => $invoices]);
    }

    public function transactions()
    {
        $student = Student::find(auth('student')->user()->id);
        $transactions = Transaction::query()->orderBy('id', 'desc')
            ->whereHas('invoice', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->with(['invoice', 'invoice.teacher', 'invoice.student', 'invoice.exam', 'invoice.institute'])
            ->paginate(10);
        return response(['transactions' => $transactions]);
    }

    /* ---- Join flows (by invite token) ---- */

    public function joinGroup($token)
    {
        $group = Group::where('token', $token)->first();
        if (!$group) {
            return response(['message' => __('messages.not_found'), 'success' => false], 404);
        }
        $student = Student::find(auth('student')->user()->id);
        $group->students()->syncWithoutDetaching([$student->id]);
        return response(['message' => __('messages.success'), 'success' => true, 'group' => $group->load('students')]);
    }

    public function joinCommunity($token)
    {
        if (empty($token)) {
            return response(['message' => __('messages.not_found'), 'success' => false], 404);
        }

        $student = Student::find(auth('student')->user()->id);
        if (!$student) {
            return response(['message' => __('messages.student_not_found'), 'success' => false], 404);
        }

        // 1. Check if token belongs to a Teacher
        $teacher = Teacher::where('invite_id', $token)->first();
        if ($teacher) {
            $student->teachers()->syncWithoutDetaching([$teacher->id]);
            return response(['message' => __('messages.success'), 'success' => true, 'teacher' => $teacher]);
        }

        // 2. Check if token belongs to an Institute
        $institute = Institute::where('invite_id', $token)->first();
        if ($institute) {
            $institute->students()->syncWithoutDetaching([$student->id]);
            return response(['message' => __('messages.success'), 'success' => true, 'teacher' => $institute]);
        }

        return response(['message' => __('messages.not_found'), 'success' => false], 404);
    }

    /* ---- Remove purchased exam ---- */

    public function removePurchasedExam(Exam $exam)
    {
        $student = Student::find(auth('student')->user()->id);
        $student->exams()->detach($exam->id);
        return response(['message' => __('messages.success'), 'success' => true]);
    }

    


}

