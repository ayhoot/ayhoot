<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\Admin\TeacherController as AdminTeacherController;
use App\Http\Controllers\Api\Admin\InstituteController as AdminInstituteController;
use App\Http\Controllers\Api\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Api\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Api\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Api\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Api\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Api\Admin\CityController as AdminCityController;
use App\Http\Controllers\Api\Admin\HeroSlideController as AdminHeroSlideController;

use App\Http\Controllers\Api\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Api\Admin\LabelController;
use App\Http\Controllers\Api\Admin\NewsController;
use App\Http\Controllers\Api\Instructor\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Api\Instructor\CourseController as TeacherCourseController;


use App\Http\Controllers\Api\Instructor\DashboardController as InstituteDashboardController;
use App\Http\Controllers\Api\Instructor\CourseController as InstituteCourseController;

use App\Http\Controllers\Api\Student\ExamController as StudentExamController;
use App\Http\Controllers\Api\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Api\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Api\Student\TicketController as StudentTicketController;



use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\Student\GameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::get('/cache', function () {
    Artisan::call('cache:clear');
});

Route::get('/test', [TeacherDashboardController::class, 'discover']);




Route::post('/login/admin', [AuthController::class, 'AdminLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/select-institute', [AuthController::class, 'selectInstitute']);
Route::post('/check-username', [AuthController::class, 'checkUsername']);
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/verify', [AuthController::class, 'verify']);
Route::post('/resend-verification', [AuthController::class, 'resend']);
Route::post('/send-otp-code', [AuthController::class, 'sendOTPCode']);
Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/update-password', [AuthController::class, 'updatePassword']);
Route::get('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');

// Payment routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/payment/buy-exam/{exam}', [PaymentController::class, 'buyExam']);
    Route::post('/payment/buy-course/{course}', [PaymentController::class, 'buyCourse']);
    Route::get('/check-exam-access/{exam}', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'checkExamAccess']);
    Route::get('/check-course-access/{course}', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'checkCourseAccess']);
});
Route::post('/invite-student', [AuthController::class, 'inviteStudent']);
Route::post('/validate-token', [AuthController::class, 'validateToken']);

// Public payment verification routes (no authentication required)
Route::get('/payment/verify/{userType}', [\App\Http\Controllers\Api\PaymentRedirectController::class, 'handlePaymentRedirect'])
    ->where('userType', 'student|teacher|institute');

// Fallback payment verification route (for backward compatibility)
Route::get('/payment/verify', [\App\Http\Controllers\Api\PaymentRedirectController::class, 'handlePaymentRedirect']);

Route::controller(PublicController::class)->group(function () {
    Route::get('home', 'home');
    Route::get('hero-slides', 'heroSlides');
    Route::get('plans', 'plans');
    Route::post('setlocale', 'setlocale');
    Route::get('getlocale', 'getlocale');
    Route::get('news', 'news');
    Route::get('news/{news}', 'showNews');
    Route::get('discover', 'discover');
    Route::get('home/categories', 'categories');
    Route::get('home/category/{category}', 'category');
    Route::get('home/institutes', 'institutes');
    Route::get('home/institute/{institute}', 'institute');
    Route::get('home/teachers', 'teachers');
    Route::get('home/teacher/{teacher}', 'teacher');
    Route::get('home/labels', 'labels');
    Route::get('home/label/{label}', 'label');
    Route::get('home/exams', 'exams');
    Route::get('home/course', 'courses');
    Route::get('home/course/{course}', 'course');
    Route::get('home/exam/{exam}', 'exam');
    Route::get('home/recommended-exams', 'recommendedExams');
    Route::get('home/top-exams', 'topExams');
    Route::get('home/recommended-courses', 'recommendedCourses');
    Route::get('home/top-courses', 'topCourses');
    Route::get('home/creators', 'creators');
    Route::get('banners', 'getBanners');
    Route::get('cities', 'getCities');
});

Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-user', [AuthController::class, 'updateUser']);
    
    // Get current authenticated user
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    
    // Permission routes
    Route::prefix('permissions')->controller(\App\Http\Controllers\Api\PermissionController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/check', 'check');
        Route::post('/check-participant-limit', 'checkParticipantLimit');
        Route::get('/usage', 'usage');
    });

    // Add a test route without role middleware
    Route::get('/test-basic-auth', function(Request $request) {
        $user = $request->user();
        return response([
            'authenticated' => $user ? true : false,
            'user_type' => $user ? class_basename($user) : null,
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'user_table' => $user ? $user->getTable() : null,
            'guards' => [
                'default' => auth()->user() ? 'has_user' : 'no_user',
                'student' => auth('student')->user() ? 'has_user' : 'no_user',
                'teacher' => auth('teacher')->user() ? 'has_user' : 'no_user',
                'institute' => auth('institute')->user() ? 'has_user' : 'no_user',
            ]
        ]);
    });

    Route::middleware('role:student')->prefix('student')->group(function () {
        // Exam listing
        Route::controller(StudentExamController::class)->group(function () {
            Route::get('exam', 'index');
        });
        Route::controller(GameController::class)->group(function () {
            Route::get('game', 'index');
        });

        Route::controller(StudentExamController::class)->group(function () {
            // purchasedExams lives on the student DashboardController, not StudentExamController.
            Route::get('purchased/exams', [StudentDashboardController::class, 'purchasedExams']);
        });

        // Exam access and gameplay
        Route::controller(\App\Http\Controllers\Api\Student\ExamAccessController::class)->group(function () {
            Route::get('exam/{exam}', 'show'); // View exam details
            Route::get('exam/{exam}/game/{game?}', 'getGame')->middleware('check.exam.access');
            Route::get('exam/{exam}/questions', 'questions')->middleware('check.exam.access');
            Route::get('exam/{exam}/access', 'checkAccess');
        });

        // Answer handling
        Route::controller(\App\Http\Controllers\Api\Student\ExamAnswerController::class)->group(function () {
            Route::post('exam/question', 'saveAnswer');
            Route::get('exam/question/{exam}/{question}/{game?}', 'getStudentAnswerToQuestion')->middleware('check.exam.access');
        });

        // Reports and results
        Route::controller(\App\Http\Controllers\Api\Student\ExamReportController::class)->group(function () {
            Route::get('exam/attended', 'getStudentExamAttended');
            Route::get('exam/exam/results/{answer}', 'getExamAttemptReport');
            Route::get('exam/debug/answer/{answerId}', 'debugAnswer');
            Route::get('exam/test-auth', 'testAuth');
            Route::get('exam/my-answers', 'getMyAnswers');
        });
        Route::controller(StudentDashboardController::class)->group(function () {
            Route::get('dashboard', 'index');
            Route::get('discover', 'discover');
            Route::get('get-exams', 'getExams');
            Route::get('get-courses', 'getCourses');
            Route::get('get-course/{course}', 'getCourse');
            Route::post('profile/update', 'updateProfile');
            Route::get('group', 'groups');

            Route::post('enroll-courses', 'enrollCourses');
            Route::get('buy-exam/{exam}', 'buyExam');
            Route::get('get-reports', 'getReports');
            Route::get('get-report/{report}', 'getReport');
            Route::post('payment/verify-buy-exam', 'verifyBuyExam');
            Route::get('notifications', 'notifications');
            Route::get('notifications/read', 'markAllNotificationsAsRead');
            Route::get('notifications/{notification}/read', 'markNotificationAsRead');
            Route::get('teacher-detail/{teacher}', 'teacher');
            Route::get('institute-detail/{institute}', 'institute');
            Route::get('group/{gorup}', 'group');
            Route::post('card/update', 'saveCard');

            // Wallet
            Route::get('invoices', 'invoices');
            Route::get('transactions', 'transactions');

            // Join flows (by invite token)
            Route::post('group/join/{token}', 'joinGroup');
            Route::post('community/join/{token}', 'joinCommunity');

            // Remove a purchased exam
            Route::get('exam/delete/{exam}', 'removePurchasedExam');
        });

        Route::controller(StudentCourseController::class)->group(function () {
            Route::get('course', 'index');
            // Literal route must be registered BEFORE the {course} wildcard, else
            // "purchased" is captured as a course id (→ 404).
            Route::get('course/purchased', 'purchased');
            Route::get('course/{course}', 'show');
        });


        Route::controller(StudentTicketController::class)->group(function () {
            Route::get('ticket', 'index');
            Route::post('ticket', 'store');
            Route::post('ticket/{ticket}/reply', 'reply');
            Route::get('ticket/{ticket}', 'show');
            Route::get('ticket/{ticket}/close', 'close');
        });
    });

    // =====================================================================
    // Unified Instructor API (serves both teacher and institute roles).
    // Replaces the legacy duplicated /teacher and /institute route groups.
    // =====================================================================
    Route::middleware(['role:teacher,institute'])->prefix('instructor')->group(function () {

        Route::controller(\App\Http\Controllers\Api\Instructor\DashboardController::class)->group(function () {
            Route::get('dashboard', 'index');
            Route::get('discover', 'discover');

            // Account / profile
            Route::post('profile/update', 'updateProfile');
            Route::post('profile/change-password', 'changePassword');
            Route::post('card/update', 'saveCard');

            // Notifications
            Route::get('notifications', 'notifications');
            Route::get('notifications/read', 'markAllNotificationsAsRead');
            Route::get('notifications/{notification}/read', 'markNotificationAsRead');

            // Billing
            Route::get('transactions', 'transactions');
            Route::get('invoices', 'invoices');
            Route::get('invoice/pay/{invoice}', 'payInvoice');
            Route::post('apply/voucher', 'applyVoucher');

            // Plans
            Route::get('plans', 'getPlans');
            Route::get('plans/{plan}', 'getPlan');
            Route::post('plan/{plan}/select', 'selectPlan');

            // Students
            Route::get('students', 'getStudents');
            Route::get('student/delete/{student}', 'removeStudent');
            Route::get('student/{student}', 'getStudentProfile');
            Route::get('student/{student}/get-course', 'getStudentCourses');
            Route::get('assigned-groups', 'assignedGroups');
            Route::get('owned-groups', 'ownedGroups');

            // Institutes (teacher-side affiliation)
            Route::get('institutes', 'getInstitutes');
            Route::get('current-institute', 'currentInstitute');
            Route::post('institutes/request-join', 'requestToJoinInstitute');
            Route::get('institutes/delete/{institute}', 'removeInstitute');
            Route::post('institute/join/{token}', 'joinInstitute');

            // Teachers (institute-side management)
            Route::get('teachers', 'teachers');
            Route::get('teachers/request', 'teacherRequest');
            Route::get('teachers/request/{teacherRequest}/accept', 'acceptTeacherRequest');
            Route::get('teachers/request/{teacherRequest}/reject', 'rejectTeacherRequest');
            Route::get('teachers/remove/{teacher}', 'removeTeacher');
            Route::post('teachers/invite', 'teacherInvite');

            // Discover / lookups (provided by the DiscoverLogic trait)
            Route::get('get-all-labels', 'getAllLabels');
            Route::get('get-all-courses', 'getAllCourses');
            Route::get('get-all-exams', 'getAllExams');
            Route::get('get-all-teachers', 'getAllTeachers');
            Route::get('get-all-groups', 'getAllGroups');
            Route::get('get-all-institutes', 'getAllInstitutes');
            Route::get('teacher-detail/{teacher}', 'teacher');
            Route::get('institute-detail/{institute}', 'institute');
            Route::get('course-detail/{course}', 'course');
            Route::get('exam-detail/{exam}', 'exam');
            Route::get('label-detail/{label}', 'label');
            Route::get('group-detail/{group}', 'group');
            Route::get('category/{category}', 'category');
        });

        // Exams (shared owner controller)
        Route::controller(\App\Http\Controllers\Api\Instructor\ExamOwnerController::class)->group(function () {
            Route::get('exam', 'index');
            Route::post('exam', 'store');
            Route::get('exam/archives/list', 'archives');
            Route::get('exam/purchased', 'purchasedExams');
            Route::get('purchased-content', 'purchasedContent');
            Route::get('exam/discover-paid', 'discoverPaidExams');
            Route::get('exam/{exam}/purchase-status', 'checkExamPurchaseStatus');
            Route::get('exam/{exam}/show', 'show');
            Route::get('exam/{exam}/questions', 'questions')->middleware('check.exam.access');
            Route::get('exam/{exam}', 'edit');
            Route::post('exam/{exam}', 'update');
            Route::get('exam/delete/{exam}', 'delete');
            Route::get('exam/archive/{exam}', 'archive');
            Route::get('exam/duplicate/{exam}', 'duplicate');
        });

        // Questions (shared owner controller)
        Route::controller(\App\Http\Controllers\Api\Instructor\QuestionOwnerController::class)->group(function () {
            Route::post('question', 'store');
            Route::post('exam/{exam}/question', 'store');
            Route::get('question/{question}', 'show');
            Route::post('question/{question}', 'update');
            Route::get('question/delete/{question}', 'delete');
            Route::get('exam/question/delete/{question}', 'delete');
        });

        // Courses
        Route::controller(\App\Http\Controllers\Api\Instructor\CourseController::class)->group(function () {
            Route::get('course', 'index');
            Route::post('course', 'store');
            Route::get('course/purchased', 'purchased');
            Route::post('course/add-content/{course}', 'addFileOrVideo');
            Route::post('course/remove-content/{course}', 'removeContent');
            Route::post('course/{course}/update-content', 'updateContent');
            Route::get('course/archive/{course}', 'archive');
            Route::get('course/delete/{course}', 'delete');
            Route::get('course/duplicate/{course}', 'duplicate');
            Route::post('course/{course}/assign-exams', 'assignExamsToCourse');
            Route::get('course/{course}/remove-exam/{exam}', 'removeExamFromCourse');
            Route::get('course/{course}/move-to-folder/{folder}', 'moveToFolder');
            Route::get('course/{course}/remove-from-folder/{folder}', 'removeCourseFromFolder');
            Route::get('course/{course}', 'show');
            Route::post('course/{course}', 'update');
        });

        // Folders
        Route::controller(\App\Http\Controllers\Api\Instructor\FolderController::class)->group(function () {
            Route::get('folder', 'index');
            Route::post('folder', 'store');
            Route::get('folder/delete/{folder}', 'delete');
            Route::post('folder/{folder}/assign-exams', 'assignExamsToFolder');
            Route::post('folder/{folder}/unassign-exams', 'unassignExamsFromFolder');
            Route::post('folder/{folder}/unassign-courses', 'unassignCoursesFromFolder');
            Route::get('folder/{folder}/courses', 'getCoursesInFolder');
            Route::get('folder/{folder}', 'show');
        });

        // Groups
        Route::controller(\App\Http\Controllers\Api\Instructor\GroupController::class)->group(function () {
            Route::get('group', 'index');
            Route::post('group', 'store');
            Route::get('group/assigned', 'getAssignedGroups');
            Route::get('group/delete/{group}', 'delete');
            Route::get('group/{student}/{group}/add', 'addStudent');
            Route::get('group/{student}/{group}/remove', 'removeStudent');
            Route::post('group/{group}/assign', 'assign');
            Route::post('group/{group}/unassign', 'unassign');
            Route::get('group/{group}', 'show');
            Route::post('group/{group}', 'update');
        });

        // Games
        Route::controller(\App\Http\Controllers\Api\Instructor\GameController::class)->group(function () {
            Route::get('game', 'index');
            Route::post('game', 'store');
            Route::get('game/delete/{game}', 'delete');
            Route::get('game/{game}/report', 'getReport');
            Route::get('game/{game}', 'edit');
            Route::post('game/{game}', 'update');
        });

        // Tickets
        Route::controller(\App\Http\Controllers\Api\Instructor\TicketController::class)->group(function () {
            Route::get('ticket', 'index');
            Route::post('ticket', 'store');
            Route::post('ticket/{ticket}/reply', 'reply');
            Route::get('ticket/{ticket}/close', 'close');
            Route::get('ticket/{ticket}', 'show');
        });

        // Purchases
        Route::post('purchase/initiate', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'initiatePurchase']);
        Route::post('purchase/verify', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPurchase']);
    });

    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {

        Route::controller(TeacherDashboardController::class)->group(function () {
            Route::get('dashboard', 'index');
            Route::get('discover', 'discover');
            Route::get('plans', 'getPlans');
            Route::get('institutes', 'getInstitutes');
            Route::get('current-institute', 'currentInstitute');
            Route::get('students', 'getStudents');
            Route::get('assigned-groups', 'assignedGroups');
            Route::get('owned-groups', 'ownedGroups');
            Route::post('password/update', 'changePassword');
            Route::get('student/delete/{student}', 'removeStudent');
            Route::post('/profile/update', 'updateProfile');
            Route::post('/profile/change-password', 'changePassword');
            Route::post('/institutes/request-join', 'requestToJoinInstitute');
            Route::get('/institutes/{institute}', 'removeInstitute');
            Route::get('plans/{plan}', 'getPlan');
            Route::post('plan/{plan}/select', 'selectPlan');
            Route::post('card/update', 'saveCard');
            Route::get('category/{category}', 'category');

            Route::get('get-all-labels', 'getAllLabels');
            Route::get('get-all-courses', 'getAllCourses');
            Route::get('get-all-exams', 'getAllExams');
            Route::get('get-all-teachers', 'getAllTeachers');
            Route::get('get-all-groups', 'getAllGroups');
            Route::get('get-all-institutes', 'getAllInstitutes');
            Route::get('teacher-detail/{teacher}', 'teacher');
            Route::get('institute-detail/{institute}', 'institute');
            Route::get('course-detail/{course}', 'course');
            Route::get('exam-detail/{exam}', 'exam');
            Route::get('label-detail/{label}', 'label');
            Route::get('group-detail/{group}', 'group');

            Route::get('transactions', 'transactions');
            Route::get('invoices', 'invoices');

            Route::post('/apply/voucher', 'applyVoucher');
            Route::get('notifications', 'notifications');
            Route::get('notifications/read', 'markAllNotificationsAsRead');
            Route::get('notifications/{notification}/read', 'markNotificationAsRead');
            Route::get('invoice/pay/{invoice}', 'payInvoice');
        });


        // Use shared exam controller for teachers
        Route::controller(\App\Http\Controllers\Api\Instructor\ExamOwnerController::class)->group(function () {
            Route::get('exam', 'index');
            Route::post('exam', 'store');
            Route::get('exam/archives/list', 'archives');
            Route::get('exam/purchased', 'purchasedExams');
            Route::get('purchased-content', 'purchasedContent');
            Route::get('exam/discover-paid', 'discoverPaidExams');
            Route::get('exam/{exam}/purchase-status', 'checkExamPurchaseStatus');
            Route::get('exam/{exam}/show', 'show'); // View exam details
            Route::get('exam/{exam}', 'edit'); // Edit exam (with access control)
            Route::get('exam/{exam}/questions', 'questions')->middleware('check.exam.access');
            Route::post('exam/{exam}', 'update');
            Route::get('exam/delete/{exam}', 'delete');
            Route::get('exam/archive/{exam}', 'archive');
            Route::get('exam/duplicate/{exam}', 'duplicate');
        });


        // Use shared question controller for teachers
        Route::controller(\App\Http\Controllers\Api\Instructor\QuestionOwnerController::class)->group(function () {
            Route::get('question/{question}', 'show');
            Route::post('question', 'store');
            Route::post('question/{question}', 'update');
            Route::get('question/delete/{question}', 'delete');
        });


        Route::controller(TeacherCourseController::class)->group(function () {
            Route::get('course', 'index');
            Route::post('course', 'store');
            Route::post('course/{course}', 'update');
            Route::get('course/archive/{course}', 'archive');
            Route::get('course/{course}', 'show');
            Route::get('course/delete/{course}', 'delete');
            Route::post('course/{course}/assign-exams', 'assignExamsToCourse');
            Route::post('course/add-content/{course}', 'addFileOrVideo');
            Route::post('course/remove-content/{course}', 'removeContent');
            Route::get('course/{course}/remove-exam/{exam}', 'removeExamFromCourse');
            Route::get('course/{course}/move-to-folder/{folder}', 'moveToFolder');
            Route::post('course/{course}/update-content', 'updateContent');
            Route::get('course/duplicate/{course}', 'duplicate');
            Route::get('course/purchased', 'purchased');
        });

    });



    Route::middleware(['role:institute'])->prefix('institute')->group(function () {

        Route::controller(InstituteDashboardController::class)->group(function () {
            Route::get('dashboard', 'index');
            Route::get('discover', 'discover');
            Route::get('plans', 'getPlans');
            Route::get('teachers/request', 'teacherRequest');
            Route::get('teachers', 'teachers');
            Route::get('teachers/request/{teacherRequest}/accept', 'acceptTeacherRequest');
            Route::get('teachers/request/{teacherRequest}/reject', 'rejectTeacherRequest');
            Route::get('teachers/remove/{teacher}', 'removeTeacher');
            Route::post('teachers/invite', 'teacherInvite');
            Route::get('students', 'getStudents');
            Route::get('assigned-groups', 'assignedGroups');
            Route::get('owned-groups', 'ownedGroups');
            Route::post('profile/update', 'updateProfile');
            Route::get('student/delete/{student}', 'removeStudent');
            Route::post('/profile/change-password', 'changePassword');
            Route::get('plans/{plan}', 'getPlan');
            Route::post('plan/{plan}/select', 'selectPlan');
            Route::post('card/update', 'saveCard');


            Route::get('get-all-labels', 'getAllLabels');
            Route::get('get-all-courses', 'getAllCourses');
            Route::get('get-all-exams', 'getAllExams');
            Route::get('get-all-teachers', 'getAllTeachers');
            Route::get('get-all-groups', 'getAllGroups');
            Route::get('get-all-institutes', 'getAllInstitutes');
            Route::get('teacher-detail/{teacher}', 'teacher');
            Route::get('institute-detail/{institute}', 'institute');
            Route::get('course-detail/{course}', 'course');
            Route::get('exam-detail/{exam}', 'exam');
            Route::get('label-detail/{label}', 'label');
            Route::get('group-detail/{group}', 'group');

            Route::get('transactions', 'transactions');
            Route::get('invoices', 'invoices');
            Route::post('/apply/voucher', 'applyVoucher');

            Route::get('notifications', 'notifications');
            Route::get('notifications/read', 'markAllNotificationsAsRead');
            Route::get('notifications/{notification}/read', 'markNotificationAsRead');
            Route::get('invoice/pay/{invoice}', 'payInvoice');
        });


        // Use shared exam controller for institutes
        Route::controller(\App\Http\Controllers\Api\Instructor\ExamOwnerController::class)->group(function () {
            Route::get('exam', 'index');
            Route::post('exam', 'store');
            Route::get('exam/archives/list', 'archives');
            Route::get('exam/purchased', 'purchasedExams');
            Route::get('purchased-content', 'purchasedContent');
            Route::get('exam/discover-paid', 'discoverPaidExams');
            Route::get('exam/{exam}/purchase-status', 'checkExamPurchaseStatus');
            Route::get('exam/{exam}/show', 'show'); // View exam details
            Route::get('exam/{exam}', 'edit'); // Edit exam (with access control)
            Route::get('exam/{exam}/questions', 'questions')->middleware('check.exam.access');
            Route::post('exam/{exam}', 'update');
            Route::get('exam/delete/{exam}', 'delete');
            Route::get('exam/archive/{exam}', 'archive');
            Route::get('exam/duplicate/{exam}', 'duplicate');
        });


        // Use shared question controller for institutes
        Route::controller(\App\Http\Controllers\Api\Instructor\QuestionOwnerController::class)->group(function () {
            Route::post('question', 'store');
            Route::get('question/{question}', 'show');
            Route::post('question/{question}', 'update');
            Route::get('question/delete/{question}', 'delete');
        });


        Route::controller(InstituteCourseController::class)->group(function () {
            Route::get('course', 'index');
            Route::post('course', 'store');
            Route::get('course/archive/{course}', 'archive');
            Route::get('course/delete/{course}', 'delete');
            Route::post('course/{course}', 'update');
            Route::get('course/{course}', 'show');
            Route::post('course/{course}/assign-exams', 'assignExamsToCourse');
            Route::post('course/add-content/{course}', 'addFileOrVideo');
            Route::post('course/remove-content/{course}', 'removeContent');
            Route::get('course/{course}/remove-exam/{exam}', 'removeExamFromCourse');
            Route::get('course/{course}/move-to-folder/{folder}', 'moveToFolder');
            Route::get('course/{course}/remove-from-folder/{folder}', 'removeCourseFromFolder');
            Route::post('course/{course}/update-content', 'updateContent');
            Route::get('course/duplicate/{course}', 'duplicate');
            Route::get('course/purchased', 'purchased');
        });

    });

    // Shared discount routes (accessible by all authenticated users)
    Route::post('discount/validate', [\App\Http\Controllers\Api\Instructor\DiscountController::class, 'validateCode']);
    Route::get('discount/active', [\App\Http\Controllers\Api\Instructor\DiscountController::class, 'getActiveDiscounts']);

    // Shared library routes (accessible by all authenticated users)
    Route::get('library', [\App\Http\Controllers\Api\Instructor\LibraryController::class, 'index']);
    Route::get('library/assignable', [\App\Http\Controllers\Api\Instructor\LibraryController::class, 'getAssignableContent']);
    Route::get('library/{contentType}/{contentId}', [\App\Http\Controllers\Api\Instructor\LibraryController::class, 'getContentForAssignment']);

    // Shared reports routes (accessible by all authenticated users)
    Route::get('reports/dashboard', [\App\Http\Controllers\Api\Instructor\ReportsController::class, 'getDashboardReports']);
    Route::get('reports', [\App\Http\Controllers\Api\Instructor\ReportsController::class, 'getReportsList']);

    // Authenticated discover routes (excludes user's own content)
    Route::get('discover', [\App\Http\Controllers\Api\Instructor\DiscoverController::class, 'discover']);
    Route::get('discover/category/{category}', [\App\Http\Controllers\Api\Instructor\DiscoverController::class, 'category']);
    Route::get('discover/exams', [\App\Http\Controllers\Api\Instructor\DiscoverController::class, 'exams']);
    Route::get('discover/courses', [\App\Http\Controllers\Api\Instructor\DiscoverController::class, 'courses']);

    // Live session routes (accessible by all authenticated users)
    Route::controller(\App\Http\Controllers\Api\Instructor\LiveSessionController::class)->group(function () {
        Route::post('live-session/create', 'create');
        Route::get('live-session/{pin}', 'getByPin');
        Route::get('live-session/id/{sessionId}', 'getById');
        Route::post('live-session/{sessionId}/start', 'start');
        Route::post('live-session/{sessionId}/start-question', 'startQuestion');
        Route::post('live-session/{sessionId}/submit-answer', 'submitAnswer');
        Route::post('live-session/{sessionId}/show-results', 'showResults');
        Route::post('live-session/{sessionId}/next-question', 'nextQuestion');
        Route::post('live-session/{sessionId}/previous-question', 'previousQuestion');
        Route::post('live-session/{sessionId}/timer-pause', 'timerPause');
        Route::post('live-session/{sessionId}/update-config', 'updateConfig');
        Route::post('live-session/{sessionId}/finish', 'finish');
        Route::get('live-session/{pin}/qr', 'getQrCode');
        Route::get('live-session/{sessionId}/participants', 'participants');
        Route::get('live-session/{sessionId}/report', 'getSessionReport');
        
        // Test broadcast endpoint
        Route::get('live-session/{sessionId}/test-broadcast', function($sessionId) {
            $session = \App\Models\LiveSession::findOrFail($sessionId);
            $participant = $session->participants()->first();
            
            if (!$participant) {
                return response()->json(['error' => 'No participants found']);
            }
            
            broadcast(new \App\Events\LiveSessionParticipantJoined($session, $participant));
            
            return response()->json([
                'message' => 'Broadcast sent',
                'channel' => 'live-session.' . $session->pin,
                'event' => 'participant.joined'
            ]);
        });
    });
});

// Live session join route (requires student authentication)
// Note: role:student middleware removed - controller checks student auth directly via Auth::guard('student')
Route::middleware(['auth:sanctum', 'throttle:60,1', \App\Http\Middleware\SetLocale::class])
    ->post('live-session/{pin}/join', [\App\Http\Controllers\Api\Instructor\LiveSessionController::class, 'join']);

// Debug endpoint to check authentication
Route::middleware(['auth:sanctum', 'throttle:60,1'])
    ->get('auth/check', function () {
        $user = auth()->user();
        $guards = [];
        foreach (['student', 'teacher', 'institute', 'admin'] as $guard) {
            $guardUser = auth($guard)->user();
            $guards[$guard] = $guardUser ? [
                'authenticated' => true,
                'id' => $guardUser->id,
                'name' => $guardUser->name ?? ($guardUser->first_name . ' ' . $guardUser->last_name),
                'model' => get_class($guardUser),
            ] : ['authenticated' => false];
        }
        
        return response()->json([
            'authenticated' => $user ? true : false,
            'user' => $user,
            'user_model' => $user ? get_class($user) : null,
            'guards' => $guards,
        ]);
    });

Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
    Route::middleware(['role:institute'])->prefix('institute')->group(function () {
        Route::post('purchase/initiate', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'initiatePurchase']);
        Route::post('purchase/verify', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPurchase']); // API verification
    });

    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        Route::post('purchase/initiate', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'initiatePurchase']);
        Route::post('purchase/verify', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPurchase']); // API verification
    });

    Route::middleware(['role:student'])->prefix('student')->group(function () {
        Route::post('purchase/initiate', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'initiatePurchase']);
        Route::post('purchase/verify', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPurchase']); // API verification
    });

    // Public payment verification routes (for Zarinpal redirects - no auth required)
    Route::get('payment/verify/institute', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPaymentRedirect']);
    Route::get('payment/verify/teacher', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPaymentRedirect']);
    Route::get('payment/verify/student', [\App\Http\Controllers\Api\Instructor\PurchaseController::class, 'verifyPaymentRedirect']);




    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::controller(AdminDashboardController::class)->group(function () {
            Route::get('dashboard', 'index');
            Route::get('transactions', 'transactions');
            Route::get('notifications', 'notifications');
            Route::get('notifications/read', 'markAllNotificationsAsRead');
            Route::get('notifications/{notification}/read', 'markNotificationAsRead');
        });

        Route::controller(AdminStudentController::class)->group(function () {
            Route::get('student', 'index');
            Route::post('student', 'store');
            Route::get('student/{student}', 'edit');
            Route::get('student/{student}/get-course', 'getCourse');
            Route::post('student/{student}', 'update');
            Route::get('student/delete/{student}', 'delete');
        });



        Route::controller(AdminTeacherController::class)->group(function () {
            Route::get('teacher', 'index');
            Route::post('teacher', 'store');
            Route::get('teacher/{teacher}', 'show');
            Route::get('teacher/delete/{teacher}', 'delete');
            Route::post('teacher/{teacher}/verify', 'verify');
            Route::post('teacher/{teacher}/unverify', 'unverify');
            Route::post('teacher/{teacher}/ban', 'ban');
            Route::post('teacher/{teacher}/unban', 'unban');

            Route::get('teacher/{teacher}/courses', 'getCourses');
            Route::get('teacher/{teacher}/exams', 'getExams');
            Route::post('teacher/{teacher}/plan', 'updatePlan');
        });

        Route::controller(AdminInstituteController::class)->group(function () {
            Route::get('institute', 'index');
            Route::post('institute', 'store');
            Route::get('institute/{institute}', 'edit');
            Route::post('institute/{institute}', 'update');
            Route::post('institute/{institute}/status', 'updateStatus');
            Route::get('institute/delete/{institute}', 'delete');


            Route::get('institute/{institute}/teachers', 'getTeachers');
            Route::get('institute/{institute}/students', 'getStudents');
            Route::get('institute/{institute}/exams', 'getExams');
            Route::get('institute/{institute}/courses', 'getCourses');
            Route::post('institute/{institute}/plan', 'updatePlan');
        });

        Route::controller(AdminPlanController::class)->group(function () {
            Route::get('plan', 'index');
            Route::get('plan/features', 'getAllFeatures');
            Route::get('plan/get/all', 'getAllPlans');
            Route::post('plan', 'store');
            Route::get('plan/{plan}', 'edit');
            Route::post('plan/{plan}', 'update');
            Route::get('plan/delete/{plan}', 'delete');
        });

        Route::controller(AdminExamController::class)->group(function () {
            Route::get('exam', 'index');
            Route::post('exam', 'store');
            Route::get('exam/{exam}', 'edit');
            Route::get('exam/{exam}/questions', 'questions');
            Route::post('exam/{exam}', 'update');
            Route::get('exam/{exam}/recommend', 'recommend');
            Route::post('exam/{exam}/status', 'updateStatus');
            Route::get('exam/delete/{exam}', 'delete');
        });


        Route::controller(AdminQuestionController::class)->group(function () {
            Route::get('exam/{exam}/question', 'index');
            Route::post('exam/{exam}/question', 'store');
            Route::get('exam/{exam}/question/{question}', 'delete');
        });

        Route::controller(AdminSettingController::class)->prefix('setting')->group(function () {
            Route::get('/', 'index');
            Route::post('/banner', 'updateBanner');
            Route::post('/discount', 'updateDiscount');
            Route::post('/change-password', 'changePassword');
            Route::get('/locale', 'getLocaleSettings');
            Route::post('/locale', 'updateLocaleSettings');
        });


        Route::controller(AdminCourseController::class)->group(function () {
            Route::get('course', 'index');
            Route::post('course', 'store');
            Route::get('course/{course}', 'edit');
            Route::post('course/{course}', 'update');
            Route::get('course/delete/{course}', 'delete');
            Route::post('course/{course}/status', 'updateStatus');
        });

        Route::controller(NewsController::class)->group(function () {
            Route::get('news', 'index');
            Route::post('news', 'store');
            Route::get('news/delete/{news}', 'destroy');
            Route::get('news/{news}', 'show');
            Route::post('news/{news}', 'update');
        });


        Route::controller(AdminActivityController::class)->group(function () {
            Route::get('activity', 'index');
        });

        Route::controller(AdminTicketController::class)->group(function () {
            Route::get('ticket', 'index');
            Route::post('ticket', 'store');
            Route::post('ticket/{ticket}/reply', 'reply');
            Route::get('ticket/{ticket}', 'show');
            Route::get('ticket/{ticket}/close', 'close');
            Route::post('ticket/{ticket}/status', 'updateStatus');
        });

        Route::controller(AdminBannerController::class)->group(function () {
            Route::get('banners', 'index');
            Route::post('banners', 'store');
            Route::get('banners/{banner}', 'show');
            Route::put('banners/{banner}', 'update');
            Route::delete('banners/{banner}', 'destroy');
            Route::patch('banners/{banner}/toggle-active', 'toggleActive');
        });

        Route::controller(AdminHeroSlideController::class)->group(function () {
            Route::get('hero-slides', 'index');
            Route::post('hero-slides', 'store');
            Route::get('hero-slides/{heroSlide}', 'show');
            Route::post('hero-slides/{heroSlide}', 'update');
            Route::delete('hero-slides/{heroSlide}', 'destroy');
            Route::patch('hero-slides/{heroSlide}/toggle-active', 'toggleActive');
            Route::post('hero-slides/reorder', 'reorder');
        });

        Route::controller(AdminCityController::class)->group(function () {
            Route::get('cities', 'index');
            Route::get('cities/all', 'getAll');
            Route::post('cities', 'store');
            Route::get('cities/{city}', 'show');
            Route::put('cities/{city}', 'update');
            Route::delete('cities/{city}', 'destroy');
        });


        Route::controller(CategoryController::class)->group(function () {
            Route::get('category', 'index');
            Route::post('category', 'store');
            Route::get('category/{category}', 'edit');
            Route::post('category/{category}', 'update');
            Route::get('category/delete/{category}', 'delete');
            Route::get('category/get/all', 'getCategories');
        });

        Route::controller(LabelController::class)->group(function () {
            Route::get('label', 'index');
            Route::post('label', 'store');
            Route::get('label/{label}', 'edit');
            Route::post('label/{label}', 'update');
            Route::get('label/delete/{label}', 'destroy');
            Route::post('label/{label}/assign-exam', 'assignExam');
            Route::post('label/{label}/remove-exam', 'removeExam');


            Route::post('label/{label}/remove-teacher', 'removeTeacher');
            Route::post('label/{label}/assign-teacher', 'assignTeacher');


            Route::post('label/{label}/remove-institute', 'removeInstitute');
            Route::post('label/{label}/assign-institute', 'assignInstitute');

            Route::post('label/{label}/remove-course', 'removeCourse');
            Route::post('label/{label}/assign-course', 'assignCourse');
        });
    });
});
