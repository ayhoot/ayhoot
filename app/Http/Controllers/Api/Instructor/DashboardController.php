<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Api\Instructor\Concerns\InteractsWithInstructor;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\Instructor\DiscoverLogic;
use App\Http\Controllers\Controller;
use App\Mail\EmailSend;
use App\Models\Activity;
use App\Models\Answer;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Folder;
use App\Models\Group;
use App\Models\Institute;
use App\Models\InstituteInvite;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\Question;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherRequest;
use App\Models\Transaction;
use App\Service\Zarinpal;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

/**
 * Unified dashboard / account controller for instructors (teacher OR institute).
 *
 * Replaces the duplicated Api\Teacher\DashboardController and
 * Api\Institute\DashboardController. Shared behaviour uses the role-aware helpers
 * from InteractsWithInstructor; genuinely role-specific behaviour branches on the
 * instructor type. The DiscoverLogic trait supplies the role-agnostic
 * detail/listing endpoints (course, teacher, institute, getAll*, discover, ...).
 */
class DashboardController extends Controller
{
    use DiscoverLogic, InteractsWithInstructor;

    public function index()
    {
        return $this->isInstitute() ? $this->instituteDashboard() : $this->teacherDashboard();
    }

    protected function teacherDashboard()
    {
        $teacher = Teacher::with('institutes', 'plan')->find($this->getInstructor()->id);
        $teacherId = $teacher->id;
        $instituteId = $this->getInstructor()->selected_institute ?? $teacher->currentAccessToken()->institute_id ?? null;

        $coursesQuery = Course::where('teacher_id', $teacherId)->whereNull('archive');
        $examsQuery = Exam::where('teacher_id', $teacherId)->whereNull('archive');
        $foldersQuery = Folder::where('teacher_id', $teacherId);

        if ($instituteId) {
            $coursesQuery->where('institute_id', $instituteId);
            $examsQuery->where('institute_id', $instituteId);
            $foldersQuery->where('institute_id', $instituteId);
        } else {
            $coursesQuery->whereNull('institute_id');
            $examsQuery->whereNull('institute_id');
            $foldersQuery->whereNull('institute_id');
        }

        $totalStudentsForTeacher = $teacher->students()->count();
        $totalCoursesForTeacher = (clone $coursesQuery)->count();
        $totalExamsForTeacher = (clone $examsQuery)->count();
        $teacherExamsByStatus = (clone $examsQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $teacherQuestionsByType = Question::where('teacher_id', $teacherId)->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type')->toArray();
        $totalGroupsForTeacher = Group::where('teacher_id', $teacherId)->whereNull('institute_id')->count();

        $instituteStudentsCount = 0;
        $instituteCoursesCount = 0;
        $instituteExamsCount = 0;
        $totalTeachersInInstitute = 0;
        $totalGroupsInInstitute = 0;
        if ($instituteId) {
            $institute = Institute::find($instituteId);
            if ($institute) {
                $instituteStudentsCount = $institute->students()->count();
                $instituteCoursesCount = (clone $coursesQuery)->count();
                $instituteExamsCount = (clone $examsQuery)->count();
                $totalTeachersInInstitute = $institute->teachers()->count();
                $totalGroupsInInstitute = Group::where('teacher_id', $teacherId)->where('institute_id', $instituteId)->count();
            }
        }

        $courses = $coursesQuery->with(['teacher', 'institute', 'category'])->orderBy('id', 'desc')->take(10)->get();
        $folders = $foldersQuery->orderBy('id', 'desc')->take(10)->get();
        $exams = $examsQuery
            ->withCount(['questions', 'games as plays_count'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $exams->transform(function ($exam) {
            $gamesCount = $exam->plays_count ?? 0;
            $answersCount = \App\Models\Answer::where('exam_id', $exam->id)->count();
            $realPlays = max($gamesCount, $answersCount);
            $exam->plays_count = $realPlays;
            $exam->students_count = $realPlays;
            return $exam;
        });

        $activities = Activity::where('teacher_id', $teacherId)->orderBy('id', 'desc')->take(10)->get();

        return response([
            'teacher' => $teacher,
            'dashboard' => [
                'teacher_data' => [
                    'total_students' => $totalStudentsForTeacher,
                    'total_courses' => $totalCoursesForTeacher,
                    'total_exams' => $totalExamsForTeacher,
                    'exams_by_status' => $teacherExamsByStatus,
                    'questions_by_type' => $teacherQuestionsByType,
                    'total_groups' => $totalGroupsForTeacher,
                ],
                'institute_data' => [
                    'total_students' => $instituteStudentsCount,
                    'total_courses' => $instituteCoursesCount,
                    'total_exams' => $instituteExamsCount,
                    'total_teachers_in_institute' => $totalTeachersInInstitute,
                    'total_groups_in_institute' => $totalGroupsInInstitute,
                ],
                'current_institute_id' => $instituteId,
            ],
            'courses' => $courses,
            'folders' => $folders,
            'exams' => $exams,
            'verified_teachers' => Teacher::where('status', 'verified')->take(10)->get(),
            'verified_institutes' => Institute::where('status', 'verified')->take(10)->get(),
            'recommended_exams' => Exam::where('teacher_id', $teacherId)->orderBy('id', 'desc')->take(10)->get(),
            'activities' => $activities,
            'message' => 'Dashboard data fetched successfully',
        ]);
    }

    protected function instituteDashboard()
    {
        $institute = Institute::with('teachers', 'plan')->find($this->getInstructor()->id);
        $instituteId = $institute->id;

        $courses = Course::where('institute_id', $instituteId)->whereNull('archive')->with(['teacher', 'institute', 'category'])->orderBy('id', 'desc')->take(10)->get();
        $exams = Exam::where('institute_id', $instituteId)
            ->whereNull('archive')
            ->withCount(['questions', 'games as plays_count'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $exams->transform(function ($exam) {
            $gamesCount = $exam->plays_count ?? 0;
            $answersCount = \App\Models\Answer::where('exam_id', $exam->id)->count();
            $realPlays = max($gamesCount, $answersCount);
            $exam->plays_count = $realPlays;
            $exam->students_count = $realPlays;
            return $exam;
        });

        $activities = Activity::where('institute_id', $instituteId)->orderBy('id', 'desc')->take(10)->get();

        return response([
            // The profile page reads `response.teacher` generically for both roles.
            'teacher' => $institute,
            'institute' => $institute,
            'dashboard' => [
                'teacher_data' => [],
                'institute_data' => [
                    'total_students' => $institute->students()->count(),
                    'total_courses' => Course::where('institute_id', $instituteId)->whereNull('archive')->count(),
                    'total_exams' => Exam::where('institute_id', $instituteId)->whereNull('archive')->count(),
                    'total_teachers_in_institute' => $institute->teachers()->count(),
                    'total_groups_in_institute' => Group::where('institute_id', $instituteId)->count(),
                ],
                'current_institute_id' => $instituteId,
            ],
            'courses' => $courses,
            'exams' => $exams,
            'activities' => $activities,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Students
     * ------------------------------------------------------------------- */

    public function getStudents(Request $request)
    {
        $instructor = $this->getInstructor();
        $column = $this->ownerColumn();

        $studentsQuery = $instructor->students();
        if ($request->search) {
            $studentsQuery->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        $students = $studentsQuery->with(['groups' => function ($query) use ($instructor, $column) {
            $query->where($column, $instructor->id);
        }])->paginate(10);

        if (empty($instructor->invite_id)) {
            $instructor->invite_id = (string) \Illuminate\Support\Str::uuid();
            $instructor->save();
        }

        return response([
            'students' => $students,
            'invite_id' => $instructor->invite_id,
            'invite_link' => url('/student/join-tutor/' . $instructor->invite_id),
        ]);
    }

    public function removeStudent(Student $student, Request $request)
    {
        $instructor = $this->getInstructor();
        if (!$student) {
            return response(['message' => __('messages.student_not_found')], 409);
        }
        $instructor->students()->detach($student->id);
        return response(['message' => __('messages.student_removed_successfully')]);
    }

    public function getStudentProfile(Student $student)
    {
        $student->load('institutes');

        $studentAnswers = Answer::where('student_id', $student->id)
            ->with(['exam.questions', 'exam.teacher', 'exam.institute', 'game'])
            ->get();

        $processedAnswers = $studentAnswers->map(function ($answer) {
            if (!$answer->exam) {
                $answer->rank = null;
                $answer->score_details = ['correct' => 0, 'total' => 0];
                return $answer;
            }

            $allAttempts = Answer::where('exam_id', $answer->exam_id)
                ->where('game_id', $answer->game_id)
                ->with('exam.questions')
                ->get();

            $scores = $allAttempts->map(function ($attempt) {
                return [
                    'student_id' => $attempt->student_id,
                    'score' => $this->calculateStudentTotalScore($attempt),
                ];
            });

            $rankedScores = $scores->sortByDesc('score')->values();

            $rankIndex = $rankedScores->search(function ($item) use ($answer) {
                return $item['student_id'] == $answer->student_id;
            });

            $answer->rank = $rankIndex !== false ? $rankIndex + 1 : null;

            $correctCount = 0;
            $totalScorableQuestions = 0;
            $answeredEntries = $answer->answers ?? [];
            $questionsKeyed = $answer->exam->questions->keyBy('id');

            foreach ($answeredEntries as $entry) {
                $question = $questionsKeyed->get($entry['question_id'] ?? null);
                if ($question && $question->type !== 'slide') {
                    $totalScorableQuestions++;
                    if ($entry['is_correct'] ?? false) {
                        $correctCount++;
                    }
                }
            }

            $answer->score_details = [
                'correct' => $correctCount,
                'total' => $totalScorableQuestions > 0 ? $totalScorableQuestions : $answer->exam->questions->where('type', '!=', 'slide')->count(),
            ];

            return $answer;
        });

        return response([
            'student' => $student,
            'answers' => $processedAnswers,
        ]);
    }

    public function getStudentCourses(Student $student)
    {
        $courses = $student->courses()->with(['institute', 'teacher'])->get();

        return response([
            'courses' => $courses,
        ]);
    }

    protected function calculateStudentTotalScore(Answer $answer): int
    {
        $totalPointsEarned = 0;
        $studentAnswerEntries = $answer->answers ?? [];
        $examQuestions = $answer->exam?->questions;

        if (empty($studentAnswerEntries) || !$examQuestions) {
            return 0;
        }

        $questionsKeyedById = $examQuestions->keyBy('id');

        foreach ($studentAnswerEntries as $entry) {
            $questionId = $entry['question_id'] ?? null;
            if (!$questionId) continue;

            $question = $questionsKeyedById->get($questionId);

            if ($question && $question->type !== 'slide') {
                $isCorrect = $entry['is_correct'] ?? false;
                if ($isCorrect) {
                    $totalPointsEarned += ($question->points > 0 ? $question->points : 1000);
                }
            }
        }

        return $totalPointsEarned;
    }

    /* ---------------------------------------------------------------------
     | Groups (assigned / owned)
     * ------------------------------------------------------------------- */

    public function assignedGroups()
    {
        $instructor = $this->getInstructor();
        if ($this->isInstitute()) {
            $groups = Group::where('institute_id', $instructor->id)->paginate(10);
        } else {
            $groups = Group::where('teacher_id', $instructor->id)->whereNotNull('institute_id')->paginate(10);
        }
        return response(['groups' => $groups]);
    }

    public function ownedGroups()
    {
        $instructor = $this->getInstructor();
        if ($this->isInstitute()) {
            $groups = Group::where('institute_id', $instructor->id)->where(function ($query) use ($instructor) {
                $query->whereNull('teacher_id')
                    ->orWhereIn('teacher_id', $instructor->teachers()->pluck('teachers.id'));
            })->paginate(10);
        } else {
            $groups = Group::where('teacher_id', $instructor->id)->whereNull('institute_id')->paginate(10);
        }
        return response(['groups' => $groups]);
    }

    /* ---------------------------------------------------------------------
     | Profile / password / card
     * ------------------------------------------------------------------- */

    public function updateProfile(Request $request)
    {
        $instructor = $this->getInstructor();

        if ($this->isInstitute()) {
            $data = app(\App\Http\Requests\Institute\UpdateProfileRequest::class)->validated();
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'avatar_' . $instructor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/institute_avatars'), $filename);
                $data['avatar'] = 'institute_avatars/' . $filename;
            }
            if ($request->hasFile('cover')) {
                $file = $request->file('cover');
                $filename = 'cover_' . $instructor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/institute_covers'), $filename);
                $data['cover'] = $filename;
            }
            $instructor->update($data);
            return response([
                'message' => __('messages.profile_updated_successfully'),
                'teacher' => $instructor,
                'institute' => $instructor,
            ]);
        }

        $data = app(\App\Http\Requests\Teacher\UpdateProfileRequest::class)->validated();
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $instructor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/teacher_avatars'), $filename);
            $data['avatar'] = $filename;
        }
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = 'cover_' . $instructor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/teacher_covers'), $filename);
            $data['cover'] = $filename;
        }
        $instructor->update($data);
        $instructor->refresh()->load('institutes');
        return response([
            'message' => __('messages.profile_updated_successfully'),
            'teacher' => $instructor,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $instructor = $this->getInstructor();
        if (!Hash::check($request->old_password, $instructor->password)) {
            return response(['message' => __('messages.current_password_incorrect')], 422);
        }
        $instructor->password = Hash::make($request->new_password);
        $instructor->save();
        return response(['message' => __('messages.password_changed_successfully')]);
    }

    public function saveCard(Request $request)
    {
        $request->validate(['card' => 'required']);
        $instructor = $this->getInstructor();
        $instructor->card = $request->card;
        $instructor->save();
        $instructor->refresh();
        return response([
            'message' => __('messages.card_saved_successfully'),
            'user' => $instructor,
            'teacher' => $instructor,
        ]);
    }

    /* ---------------------------------------------------------------------
     | Institutes (teacher-side affiliation)
     * ------------------------------------------------------------------- */

    public function getInstitutes()
    {
        $this->abortUnlessTeacher();
        return response(['institutes' => $this->getInstructor()->institutes]);
    }

    public function currentInstitute()
    {
        $this->abortUnlessTeacher();
        $selectedInstitute = $this->getInstructor()->selected_institute ?? null;
        $institute = $selectedInstitute ? Institute::find($selectedInstitute) : null;
        return response(['institute' => $institute]);
    }

    public function requestToJoinInstitute(Request $request)
    {
        $this->abortUnlessTeacher();
        $request->validate(['institute_id' => 'required|exists:institutes,id']);
        $teacher = $this->getInstructor();
        $instituteId = $request->input('institute_id');

        if ($teacher->institutes()->where('institute_id', $instituteId)->exists()) {
            return response(['message' => __('messages.user_already_member')], 422);
        }

        $institute = Institute::find($instituteId);
        $teacherRequest = TeacherRequest::where('institute_id', $instituteId)->where('teacher_id', $teacher->id)->firstOrNew();
        $teacherRequest->institute_id = $instituteId;
        $teacherRequest->teacher_id = $teacher->id;
        $teacherRequest->save();

        $subject = __('mail.teacher_join_request_subject');
        $templateContent = __('mail.teacher_join_request_body', ['teacherName' => $teacher->name, 'teacherEmail' => $teacher->email]);
        try {
            Mail::to($institute->email)->send(new EmailSend($subject, $templateContent));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send teacher join request email: " . $e->getMessage());
        }

        return response(['message' => __('messages.teacher_join_request_sent')]);
    }

    public function removeInstitute(Institute $institute)
    {
        $this->abortUnlessTeacher();
        $teacher = $this->getInstructor();
        if ($teacher->institutes()->where('institute_id', $institute->id)->exists()) {
            $teacher->institutes()->detach($institute->id);
            return response(['message' => __('messages.institute_affiliation_removed_successfully')]);
        }
        return response(['message' => __('messages.affiliation_not_found')], 409);
    }

    /** Teacher accepts an institute invitation via its token. */
    public function joinInstitute($token)
    {
        if (!$this->isTeacher()) {
            return response(['message' => __('messages.unauthorized')], 403);
        }
        $invite = InstituteInvite::where('token', $token)->first();
        if (!$invite) {
            $institute = Institute::where('invite_id', $token)->first();
            if ($institute) {
                $teacher = $this->getInstructor();
                $teacher->institutes()->syncWithoutDetaching([$institute->id]);
                return response([
                    'message' => __('messages.success'),
                    'success' => true,
                    'institute' => $institute,
                ]);
            }
            return response(['message' => __('messages.not_found'), 'success' => false], 404);
        }
        $teacher = $this->getInstructor();
        $teacher->institutes()->syncWithoutDetaching([$invite->institute_id]);
        $invite->update(['accepted_at' => now()]);

        return response([
            'message' => __('messages.success'),
            'success' => true,
            'institute' => Institute::find($invite->institute_id),
        ]);
    }

    /* ---------------------------------------------------------------------
     | Teachers (institute-side management)
     * ------------------------------------------------------------------- */

    public function teachers(Request $request)
    {
        $this->abortUnlessInstitute();
        $institute = $this->getInstructor();
        $teachers = $institute->teachers();
        if ($request->search) {
            $teachers = $teachers->where('first_name', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $teachers = $teachers->where('status', $request->status);
        }
        $teachers = $teachers->withCount(['courses', 'exams'])->orderBy('id', 'desc')->paginate(10);

        if (empty($institute->invite_id)) {
            $institute->invite_id = (string) Str::uuid();
            $institute->save();
        }

        return response([
            'teachers' => $teachers,
            'invite_id' => $institute->invite_id,
            'invite_link' => url('/join-institute/' . $institute->invite_id),
        ]);
    }

    public function teacherRequest()
    {
        $this->abortUnlessInstitute();
        $institute = $this->getInstructor();
        $teacherRequests = TeacherRequest::where('institute_id', $institute->id)
            ->with('teacher')
            ->orderBy('id', 'desc')
            ->paginate(10);
        return response(['teacherRequests' => $teacherRequests]);
    }

    public function acceptTeacherRequest(Request $request, TeacherRequest $teacherRequest)
    {
        $this->abortUnlessInstitute();
        $teacher = Teacher::find($teacherRequest->teacher_id);
        if ($teacher) {
            $teacher->institutes()->attach($teacherRequest->institute_id);
        }
        $teacherRequest->status = 'accepted';
        $teacherRequest->save();
        return response(['message' => __('messages.teacher_request_accepted_successfully')]);
    }

    public function rejectTeacherRequest(TeacherRequest $teacherRequest)
    {
        $this->abortUnlessInstitute();
        $teacherRequest->status = 'rejected';
        $teacherRequest->save();
        return response(['message' => __('messages.teacher_request_rejected_successfully')]);
    }

    public function removeTeacher(Teacher $teacher)
    {
        $this->abortUnlessInstitute();
        $institute = $this->getInstructor();
        $institute->teachers()->detach($teacher->id);
        return response(['message' => __('messages.teacher_removed_successfully')]);
    }

    public function teacherInvite(\App\Http\Requests\Institute\InstituteInviteRequest $request)
    {
        $this->abortUnlessInstitute();
        $institute = $this->getInstructor();
        $email = $request->input('email');

        $existingTeacher = Teacher::where('email', $email)->first();
        if ($existingTeacher && $existingTeacher->institutes()->where('institute_id', $institute->id)->exists()) {
            return response(['message' => __('messages.user_already_member')], 409);
        }

        $existingInvite = InstituteInvite::where('email', $email)
            ->where('institute_id', $institute->id)
            ->whereNull('accepted_at')
            ->exists();
        if ($existingInvite) {
            return response(['message' => __('messages.invitation_already_sent')], 409);
        }

        $token = Str::random(40);
        InstituteInvite::create([
            'email' => $email,
            'institute_id' => $institute->id,
            'token' => $token,
            'sent_at' => now(),
        ]);

        $subject = __('mail.institute_invite_teacher_subject');
        $templateContent = __('mail.institute_invite_teacher_body', ['instituteName' => $institute->name]);
        try {
            Mail::to($email)->send(new EmailSend($subject, $templateContent));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send invite email to {$email}: " . $e->getMessage());
        }

        $institute->notifications()->create([
            'type' => 'institute_invite_teacher',
            'data' => ['email' => $email, 'institute_id' => $institute->id],
            'message' => 'New teacher invited',
            'locale' => app()->getLocale(),
        ]);

        return response([
            'message' => __('messages.invitation_sent_successfully', ['email' => $email]),
            'invite_link' => url('/join-institute/' . $token),
            'token' => $token,
        ], 200);
    }

    /* ---------------------------------------------------------------------
     | Plans & billing
     * ------------------------------------------------------------------- */

    public function getPlans()
    {
        $plans = Plan::with('features')->where('status', 'active')->get();
        $popularPlanIndex = $plans->search(fn($plan) => $plan->is_popular);
        if ($popularPlanIndex !== false && $popularPlanIndex !== 1 && $plans->count() >= 3) {
            $popularPlan = $plans->pull($popularPlanIndex);
            $plans->splice(1, 0, [$popularPlan]);
        }
        $formattedPlans = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->title,
                'price' => $plan->is_free ? 'Free' : number_format($plan->price),
                'raw_price' => (float) $plan->price,
                'is_free' => (bool) $plan->is_free,
                'description' => $plan->description,
                'features' => $plan->features->pluck('title')->all(),
                'raw_features' => $plan->features->map(fn($f) => [
                    'id' => $f->id,
                    'role' => $f->role,
                    'value' => $f->value,
                    'title' => $f->title,
                ])->all(),
                'isPopular' => (bool) $plan->is_popular,
                'ctaText' => null,
            ];
        });
        return response(['plans' => $formattedPlans]);
    }

    public function getPlan(Plan $plan)
    {
        $planData = $plan->toArray();
        $planData['price'] = (float) $plan->price;
        $planData['duration_options'] = [
            ['label' => '1 Month', 'value' => 1, 'discount' => 0],
            ['label' => '3 Months', 'value' => 3, 'discount' => 0.10],
            ['label' => '1 Year', 'value' => 12, 'discount' => 0.20],
        ];
        return response(['plan' => $planData]);
    }

    public function selectPlan(Request $request, Plan $plan, PaymentController $paymentController)
    {
        $validator = Validator::make($request->all(), [
            'duration' => 'required|integer|in:1,3,12',
            'use_wallet' => 'required|boolean',
            'voucher_code' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $instructor = $this->getInstructor();
        $column = $this->ownerColumn();
        $type = $this->getInstructorType();
        $data = $validator->validated();

        $existingInvoice = Invoice::where($column, $instructor->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subMinutes(30))
            ->first();

        if ($existingInvoice) {
            $existingDuration = $existingInvoice->meta['duration'] ?? null;
            if ((int) $existingDuration == (int) $data['duration']) {
                self::payInvoice($existingInvoice);
            }
        }

        try {
            $finalAmount = $paymentController->calculateFinalPrice($plan, $instructor, $data);
        } catch (\Exception $e) {
            return response(['error' => $e->getMessage()], 422);
        }

        if ($finalAmount <= 0) {
            $instructor->plan_id = $plan->id;
            $instructor->save();
            return response(['message' => __('messages.subscription_activated'), 'redirect_url' => "/{$type}/dashboard"]);
        }

        $invoice = Invoice::create([
            $column => $instructor->id,
            'plan_id' => $plan->id,
            'invoice_number' => uniqid('inv-'),
            'amount' => $finalAmount,
            'status' => 'pending',
            'description' => "Subscription to {$plan->title} for {$data['duration']} months.",
            'currency' => config('zarinpal.currency', 'IRT'),
            'meta' => $data,
        ]);

        return self::payInvoice($invoice);
    }

    public static function payInvoice(Invoice $invoice)
    {
        $zarinpal = new Zarinpal();
        $result = $zarinpal->request($invoice->amount, "Invoice: " . $invoice->invoice_number);

        if ($result['success']) {
            Transaction::create([
                'invoice_id' => $invoice->id,
                'gateway' => 'zarinpal',
                'transaction_id' => $result['authority'],
                'amount' => $invoice->amount,
                'status' => 'pending',
            ]);
            return response(['payment_url' => $result['start_pay_url']]);
        }

        return response(['error' => $result['message'], 'code' => $result['code'] ?? null]);
    }

    public function applyVoucher(Request $request)
    {
        $voucherCode = $request->voucher_code;
        $planId = $request->plan_id;
        $duration = $request->duration ?? 1;

        $settings = Setting::first();
        $discounts = $settings->discount_settings ?? [];
        if (empty($discounts)) {
            return response(['message' => __('messages.voucher_not_found')], 409);
        }

        $plan = Plan::find($planId);
        if (!$plan) {
            return response(['message' => __('messages.plan_not_found')], 409);
        }

        foreach ($discounts as $discount) {
            if ($discount['code'] == $voucherCode) {
                if (isset($discount['startDate'], $discount['endDate'])) {
                    $startDate = new DateTime($discount['startDate']);
                    $endDate = new DateTime($discount['endDate']);
                    $currentDate = new DateTime();
                    if ($currentDate < $startDate || $currentDate > $endDate) {
                        return response(['message' => __('messages.voucher_not_active')], 409);
                    }
                }

                $applicablePlans = $discount['plans'] ?? [];
                if (!empty($applicablePlans) && !in_array($planId, $applicablePlans)) {
                    return response(['message' => __('messages.voucher_not_applicable_to_plan')], 409);
                }

                $percentage = $discount['percentage'] ?? 0;
                $basePrice = $plan->price * $duration;

                $durationDiscountRate = 0;
                if ($duration == 3) $durationDiscountRate = 0.10;
                if ($duration == 12) $durationDiscountRate = 0.20;

                $priceAfterDurationDiscount = $basePrice * (1 - $durationDiscountRate);
                $discountAmount = ($priceAfterDurationDiscount * $percentage) / 100;

                return response([
                    'percentage' => $percentage,
                    'discount_amount' => $discountAmount,
                    'original_price' => $priceAfterDurationDiscount,
                    'final_price' => $priceAfterDurationDiscount - $discountAmount,
                    'message' => __('messages.voucher_applied_successfully'),
                ]);
            }
        }

        return response(['message' => __('messages.voucher_not_found')], 409);
    }

    public function transactions()
    {
        $instructor = $this->getInstructor();
        $column = $this->ownerColumn();
        $transactions = Transaction::query()->orderBy('id', 'desc')
            ->whereHas('invoice', function ($query) use ($instructor, $column) {
                $query->where($column, $instructor->id);
            })
            ->with(['invoice', 'invoice.teacher', 'invoice.student', 'invoice.exam', 'invoice.institute'])
            ->paginate(10);
        return response(['transactions' => $transactions]);
    }

    public function invoices()
    {
        $instructor = $this->getInstructor();
        $invoices = Invoice::where($this->ownerColumn(), $instructor->id)
            ->orderBy('id', 'desc')
            ->with(['transactions', 'teacher', 'student', 'exam', 'institute', 'game', 'course'])
            ->paginate(10);
        return response(['invoices' => $invoices]);
    }

    public function getAllInstitutes(Request $request)
    {
        if ($this->isTeacher()) {
            $teacherId = $this->getInstructor()->id;
            $institutes = Institute::query()
                ->with(['plan'])
                ->withCount(['teachers', 'courses', 'students'])
                ->withExists([
                    'groupAssignments as is_assigned' => function ($query) use ($teacherId) {
                        $query->where('group_assignments.teacher_id', $teacherId);
                    },
                ])
                ->orderBy('id', 'desc');
        } else {
            $institutes = Institute::query()
                ->with(['plan'])
                ->withCount(['teachers', 'courses', 'students'])
                ->orderBy('id', 'desc');
        }

        if ($request->has('search')) {
            $institutes->where('name', 'like', "%{$request->search}%");
        }
        return response(['institutes' => $institutes->paginate(10)]);
    }

    /* ---------------------------------------------------------------------
     | Notifications
     * ------------------------------------------------------------------- */

    public function notifications(Request $request)
    {
        return response(['notifications' => $this->getInstructor()->notifications()->recent()->get()]);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        $this->getInstructor()->markAllNotificationsAsRead();
        return response(['message' => __('messages.notifications_read_successfully')]);
    }

    public function markNotificationAsRead(Request $request, Notification $notification)
    {
        $notification->markAsRead();
        return response(['message' => __('messages.notification_read_successfully')]);
    }
}
