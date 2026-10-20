<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Requests\Shared\UpdateExamRequest;
use App\Models\Exam;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Unified Exam Controller for Teachers and Institutes
 */
class ExamOwnerController extends ContentOwnerController
{
    protected $questionService;

    public function __construct()
    {
        $this->questionService = new QuestionService();
    }

    /**
     * Get list of owned exams
     */
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = $this->getOwnedContentQuery('exam', $user)
            ->where('archive', null)
            ->withCount(['questions', 'students', 'games as plays_count'])
            ->with(['teacher', 'institute', 'category'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->search) {
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%'));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('purchased')) {
            $query->where('purchased', 'yes');
        }

        $exams = $query->paginate(10);
        $exams->getCollection()->transform(function ($exam) {
            $gamesCount = $exam->plays_count ?? 0;
            $answersCount = \App\Models\Answer::where('exam_id', $exam->id)->count();
            $realPlays = max($gamesCount, $answersCount);
            $exam->plays_count = $realPlays;
            $exam->students_count = $realPlays;
            return $exam;
        });

        return response(['exams' => $exams]);
    }

    /**
     * Show exam for editing
     */
    public function edit(Exam $exam)
    {
        $accessInfo = $this->getContentAccess($exam);
        $examData = $exam->load($accessInfo['has_access'] ? 'questions' : []);

        return response(array_merge(['exam' => $examData], $accessInfo));
    }

    /**
     * Show exam details
     */
    public function show(Exam $exam)
    {
        $accessInfo = $this->getContentAccess($exam);
        return response(array_merge(['exam' => $exam], $accessInfo));
    }

    /**
     * Update exam
     */
    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $this->validateOwnership($exam);
        
        $user = $this->getAuthenticatedUser();
        $data = $request->validated();
        
        // Handle cover upload
        if ($request->hasFile('cover')) {
            $data['cover'] = $this->handleFileUpload($request, 'cover', 'exams', $exam->cover);
        }
        
        // Institute ownership constraints
        if ($user instanceof \App\Models\Institute) {
            $data['institute_id'] = $user->id;
        } elseif ($user instanceof \App\Models\Teacher) {
            if (array_key_exists('institute_id', $data) && $data['institute_id']) {
                $isAffiliated = $user->institutes()->where('institutes.id', $data['institute_id'])->exists();
                if (!$isAffiliated) {
                    return response(['message' => __('messages.unauthorized_access')], 403);
                }
            }
        }
        
        $exam->update($data);
        $exam->refresh();
        $exam->load(['category', 'teacher', 'institute']);
        
        return response([
            'message' => __('messages.exam_updated_successfully'), 
            'exam' => $exam
        ]);
    }

    /**
     * Create new exam
     */
    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        
        // Check permission using the service
        $permissionService = app(\App\Services\PermissionService::class);
        $canCreate = $permissionService->canCreateExam($user);
        
        if (!$canCreate['allowed']) {
            return response([
                'message' => $canCreate['reason'],
                'permission_denied' => true,
                'details' => $canCreate
            ], 403);
        }
        
        // Validate based on user type
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monetize' => 'required|in:free,paid',
            'price' => 'required_if:monetize,paid|nullable|numeric|min:0',
            'status' => 'required|in:published,pending,rejected',
            'cover' => 'nullable|image|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'play_music' => 'nullable|boolean'
        ];
        
        $data = $request->validate($rules);
        
        // Handle cover upload
        if ($request->hasFile('cover')) {
            $data['cover'] = $this->handleFileUpload($request, 'cover', 'exams');
        }
        
        // Set ownership
        $data = $this->getContentCreationData($data, $user);
        
        $exam = Exam::create($data);
        
        return response([
            'exam' => $exam, 
            'message' => __('messages.exam_created_successfully'),
            'permission_info' => $canCreate
        ], 201);
    }

    /**
     * Delete exam
     */
    public function delete(Exam $exam)
    {
        $this->validateOwnership($exam);
        $exam->delete();
        
        return response(['message' => __('messages.exam_deleted_successfully')]);
    }

    /**
     * Get exam questions
     */
    public function questions(Exam $exam)
    {
        $user = $this->getAuthenticatedUser();
        
        try {
            $questions = $this->questionService->getExamQuestions($exam->id, $user, true);
            return response(['questions' => $questions]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * Archive/unarchive exam
     */
    public function archive(Exam $exam)
    {
        $this->validateOwnership($exam);
        
        $exam->archive = $exam->archive ? null : 'yes';
        $exam->save();
        
        return response([
            'message' => __('messages.exam_status_updated_successfully'), 
            'exam' => $exam
        ]);
    }

    /**
     * Duplicate exam
     */
    public function duplicate(Exam $exam)
    {
        try {
            $newExam = $this->duplicateContent($exam);
            
            // Duplicate questions
            foreach ($exam->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->exam_id = $newExam->id;
                $newQuestion->teacher_id = $newExam->teacher_id;
                $newQuestion->institute_id = $newExam->institute_id;
                $newQuestion->save();
            }
            
            return response([
                'message' => __('messages.exam_duplicated_successfully'), 
                'exam' => $newExam
            ], 201);
            
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * Get purchased exams
     */
    public function purchasedExams(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = $this->getPurchasedContent('exam', $user, $request);
        
        return response(['exams' => $query->paginate(10)]);
    }

    /**
     * Get all purchased content (exams and courses)
     */
    public function purchasedContent(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $allPurchasedContent = $this->getCombinedPurchasedContent($user, $request);
        
        // Apply pagination
        $perPage = $request->get('per_page', 12);
        $currentPage = $request->get('page', 1);
        $total = $allPurchasedContent->count();
        $items = $allPurchasedContent->forPage($currentPage, $perPage);

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        // Get counts for summary
        $examCount = $allPurchasedContent->where('content_type', 'exam')->count();
        $courseCount = $allPurchasedContent->where('content_type', 'course')->count();

        return response([
            'purchased_content' => $paginated,
            'summary' => [
                'total_exams' => $examCount,
                'total_courses' => $courseCount,
                'total_content' => $total,
                'current_filter' => $request->get('type', 'all')
            ],
            'filters' => [
                'all' => $total,
                'exams' => $examCount,
                'courses' => $courseCount
            ]
        ]);
    }

    /**
     * Discover paid exams
     */
    public function discoverPaidExams(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = $this->getDiscoverablePaidContent('exam', $user, $request);
        
        $exams = $query->paginate(10);
        
        // Add purchase status
        $this->addPurchaseStatus($exams->getCollection(), $user);
        
        return response(['exams' => $exams]);
    }

    /**
     * Check exam purchase status
     */
    public function checkExamPurchaseStatus(Exam $exam)
    {
        $accessInfo = $this->getContentAccess($exam);
        return response($accessInfo);
    }

    /**
     * Get archived exams
     */
    public function archives(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $query = $this->getOwnedContentQuery('exam', $user)
            ->where('archive', 'yes')
            ->withCount(['questions', 'students', 'games as plays_count'])
            ->with(['teacher', 'institute', 'category'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->search) {
            $query->where(fn($q) => $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%'));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $exams = $query->paginate(10);
        $exams->getCollection()->transform(function ($exam) {
            $gamesCount = $exam->plays_count ?? 0;
            $answersCount = \App\Models\Answer::where('exam_id', $exam->id)->count();
            $realPlays = max($gamesCount, $answersCount);
            $exam->plays_count = $realPlays;
            $exam->students_count = $realPlays;
            return $exam;
        });

        return response(['exams' => $exams]);
    }
}