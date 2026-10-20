<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Admin;
use App\Models\Answer;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return response([
            'stats' => $this->getStatCardData(),
            'charts' => [
                'signups' => $this->getSignupChartData(),
                'assignments' => $this->getChartDataForModel(Answer::class, 'assignments'),
            ],
            'latest_purchases' => $this->getLatestPurchases(),
        ]);
    }

    private function getStatCardData()
    {
        $calculateChange = function ($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 1);
        };

        $currentMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $studentsCurrent = Student::where('created_at', '>=', $currentMonthStart)->count();
        $teachersCurrent = Teacher::where('created_at', '>=', $currentMonthStart)->count();
        $institutesCurrent = Institute::where('created_at', '>=', $currentMonthStart)->count();

        $studentsLast = Student::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $teachersLast = Teacher::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $institutesLast = Institute::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalInstitutes = Institute::count();
        $totalExams = Exam::count();
        $totalCourses = Course::count();

        return [
            'institutes' => [
                'total' => $totalInstitutes,
                'change' => $calculateChange($institutesCurrent, $institutesLast),
                'premium' => Institute::whereNotNull('plan_id')->count(),
            ],
            'teachers' => [
                'total' => $totalTeachers,
                'change' => $calculateChange($teachersCurrent, $teachersLast),
                'premium' => Teacher::whereNotNull('plan_id')->count(),
            ],
            'students' => [
                'total' => $totalStudents,
                'change' => $calculateChange($studentsCurrent, $studentsLast),
            ],
            'ayhoots' => [
                'total' => $totalExams,
                'change' => 0,
            ],
            'distribution' => [
                ['name' => 'Students', 'value' => $totalStudents, 'color' => '#7797FE'],
                ['name' => 'Teachers', 'value' => $totalTeachers, 'color' => '#FF8777'],
                ['name' => 'Institutes', 'value' => $totalInstitutes, 'color' => '#AE7BF2'],
            ],
            'content_overview' => [
                'ayhoots' => $totalExams,
                'courses' => $totalCourses,
                'ayhoots_percent' => ($totalExams + $totalCourses > 0) ? round(($totalExams / ($totalExams + $totalCourses)) * 100, 1) : 0,
                'courses_percent' => ($totalExams + $totalCourses > 0) ? round(($totalCourses / ($totalExams + $totalCourses)) * 100, 1) : 0,
            ],
        ];
    }

    private function getSignupChartData()
    {
        return [
            'weekly' => [
                'students' => $this->getChartDataForModel(Student::class, 'students', 'weekly'),
                'teachers' => $this->getChartDataForModel(Teacher::class, 'teachers', 'weekly'),
                'institutes' => $this->getChartDataForModel(Institute::class, 'institutes', 'weekly'),
            ],
            'monthly' => [
                'students' => $this->getChartDataForModel(Student::class, 'students', 'monthly'),
                'teachers' => $this->getChartDataForModel(Teacher::class, 'teachers', 'monthly'),
                'institutes' => $this->getChartDataForModel(Institute::class, 'institutes', 'monthly'),
            ],
            'yearly' => [
                'students' => $this->getChartDataForModel(Student::class, 'students', 'yearly'),
                'teachers' => $this->getChartDataForModel(Teacher::class, 'teachers', 'yearly'),
                'institutes' => $this->getChartDataForModel(Institute::class, 'institutes', 'yearly'),
            ],
        ];
    }

    private function getChartDataForModel($modelClass, $name, $range = 'monthly')
    {
        $query = $modelClass::query();
        $results = [];

        if ($range =='weekly') {
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $data = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as value'))
                ->groupBy('date')->orderBy('date')->pluck('value', 'date');

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $results[] = ['name' => $date->format('D'), 'value' => $data->get($date->format('Y-m-d'), 0)];
            }
        } elseif ($range =='monthly') {
            $endDate = Carbon::now()->endOfMonth();
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $data = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as value'))
                ->groupBy('month')->orderBy('month')->pluck('value', 'month');

            for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
                $results[] = ['name' => $date->format('M'), 'value' => $data->get($date->format('Y-m'), 0)];
            }
        } elseif ($range == 'yearly') {
            $endDate = Carbon::now()->endOfYear();
            $startDate = Carbon::now()->subYears(2)->startOfYear();
            $data = $query->whereBetween('created_at', [$startDate, $endDate])
                ->select(DB::raw('YEAR(created_at) as year'), DB::raw('count(*) as value'))
                ->groupBy('year')->orderBy('year')->pluck('value', 'year');

            for ($date = $startDate->copy(); $date <= $endDate; $date->addYear()) {
                $results[] = ['name' => $date->format('Y'), 'value' => $data->get($date->format('Y'), 0)];
            }
        }
        return $results;
    }

    private function getLatestPurchases()
    {
        return Invoice::with(['plan', 'teacher', 'institute'])
            ->whereNotNull('plan_id')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($invoice) {
                $user = $invoice->teacher ?? $invoice->institute;
                return [
                    'id' => $invoice->id,
                    'name' => $user->name ?? 'N/A',
                    'type' => $invoice->teacher ? 'Teacher' : 'Institute',
                    'plan' => $invoice->plan->title ?? 'N/A',
                    'amount' => $invoice->amount,
                    'invoice' => $invoice ?? null,
                    'date' => $invoice->created_at->format('Y-m-d'),
                    'avatar' => $user->avatar ?? 'https://i.pravatar.cc/150?img=' . $invoice->id,
                ];
            });
    }


    public function transactions(Request $request)
    {
        $transactions = Transaction::query()->with(['invoice', 'invoice.exam', 'invoice.student', 'invoice.game']);
        if ($request->search) {
            $transactions = $transactions->where('transaction_id', 'like', "%{$request->search}%");
        }
        if ($request->status) {
            $transactions = $transactions->where('status', $request->status);
        }
        return response([
            'transactions' => $transactions->paginate(10)
        ]);
    }
    public function notifications(Request $request)
    {
        $admin=Admin::find(auth('admin')->user()->id);
        $notifications = $admin->notifications()->recent()->get();
        return response(['notifications' => $notifications]);
    }
    public function markAllNotificationsAsRead(Request $request)
    {
        $admin=Admin::find(auth('admin')->user()->id);
        $admin->markAllNotificationsAsRead();
        return response(['message' => __('messages.notifications_read_successfully')]);
    }
    public function markNotificationAsRead(Request $request, Notification $notification)
    {
        $notification->markAsRead();
        return response(['message' => __('messages.notification_read_successfully')]);
    }
   
}
