<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Models\Question;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionOwnerController extends ContentOwnerController
{
    protected $questionService;

    public function __construct()
    {
        $this->questionService = new QuestionService();
    }

    public function store(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUser();
            $data = $request->all();

            Log::info('Files in request:', [
                'hasFiles' => $request->hasFile('files'),
                'allFiles' => $request->allFiles(),
                'requestKeys' => array_keys($request->all())
            ]);

            $uploadedFiles = [];

            foreach ($request->allFiles() as $key => $file) {
                if (preg_match('/^files\[\d+\]$/', $key)) {
                    if (is_array($file)) {
                        $uploadedFiles = array_merge($uploadedFiles, $file);
                    } else {
                        $uploadedFiles[] = $file;
                    }
                }
            }

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                if (is_array($files)) {
                    $uploadedFiles = array_merge($uploadedFiles, $files);
                } else {
                    $uploadedFiles[] = $files;
                }
            }

            $optionFiles = $request->file('option_files', []);
            if (!is_array($optionFiles)) {
                $optionFiles = [];
            }

            $optionImages = [];
            foreach ($request->allFiles() as $key => $file) {
                if (preg_match('/^option_image_(\d+)$/', $key, $matches)) {
                    $optionIndex = $matches[1];
                    $optionImages["option_image_{$optionIndex}"] = $file;
                    Log::info("Found option image for index {$optionIndex}");
                }
            }

            if (!empty($uploadedFiles)) {
                $data['uploaded_files'] = $uploadedFiles;
            }

            if (!empty($optionFiles)) {
                $data['option_files'] = $optionFiles;
            }

            if (!empty($optionImages)) {
                if (!isset($data['uploaded_files'])) {
                    $data['uploaded_files'] = [];
                }
                $data['uploaded_files'] = array_merge($data['uploaded_files'], $optionImages);
            }

            $question = $this->questionService->createQuestion(
                $request->exam_id,
                $data,
                $user
            );

            return response([
                'message' => __('messages.question_created_successfully'),
                'question' => $question
            ], 201);
        } catch (\Exception $e) {
            Log::error('Question creation error: ' . $e->getMessage());
            return response([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show(Question $question)
    {
        try {
            $user = $this->getAuthenticatedUser();

            if (!$this->ownsContent($question->exam, $user)) {
                abort(403, __('messages.unauthorized_access'));
            }

            return response(['question' => $question->load('exam')]);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 403);
        }
    }

    public function update(Request $request, Question $question)
    {
        try {
            $user = $this->getAuthenticatedUser();
            $data = $request->all();

            Log::info('Update - Files in request:', [
                'hasFiles' => $request->hasFile('files'),
                'allFiles' => $request->allFiles(),
                'requestKeys' => array_keys($request->all())
            ]);

            $uploadedFiles = [];

            foreach ($request->allFiles() as $key => $file) {
                if (preg_match('/^files\[\d+\]$/', $key)) {
                    if (is_array($file)) {
                        $uploadedFiles = array_merge($uploadedFiles, $file);
                    } else {
                        $uploadedFiles[] = $file;
                    }
                }
            }

            if ($request->hasFile('files')) {
                $files = $request->file('files');
                if (is_array($files)) {
                    $uploadedFiles = array_merge($uploadedFiles, $files);
                } else {
                    $uploadedFiles[] = $files;
                }
            }

            $optionFiles = $request->file('option_files', []);
            if (!is_array($optionFiles)) {
                $optionFiles = [];
            }

            $optionImages = [];
            foreach ($request->allFiles() as $key => $file) {
                if (preg_match('/^option_image_(\d+)$/', $key, $matches)) {
                    $optionIndex = $matches[1];
                    $optionImages["option_image_{$optionIndex}"] = $file;
                    Log::info("Found option image for index {$optionIndex}");
                }
            }

            if (!empty($uploadedFiles)) {
                $data['uploaded_files'] = $uploadedFiles;
            }

            if (!empty($optionFiles)) {
                $data['option_files'] = $optionFiles;
            }

            if (!empty($optionImages)) {
                if (!isset($data['uploaded_files'])) {
                    $data['uploaded_files'] = [];
                }
                $data['uploaded_files'] = array_merge($data['uploaded_files'], $optionImages);
            }

            $updatedQuestion = $this->questionService->updateQuestion(
                $question->id,
                $data,
                $user
            );

            return response([
                'message' => __('messages.question_updated_successfully'),
                'question' => $updatedQuestion
            ]);
        } catch (\Exception $e) {
            Log::error('Question update error: ' . $e->getMessage());
            return response([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function delete(Question $question)
    {
        try {
            $user = $this->getAuthenticatedUser();
            $this->questionService->deleteQuestion($question->id, $user);

            return response([
                'message' => __('messages.question_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}