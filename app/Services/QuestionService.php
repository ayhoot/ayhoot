<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Exam;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionService
{
    public function createQuestion($examId, $data, $user)
    {
        try {
            $exam = Exam::findOrFail($examId);

            if (!$this->canManageExam($user, $exam)) {
                throw new \Exception('Unauthorized to manage this exam');
            }

            $questionData = $this->prepareQuestionData($data, $exam, $user);
            $question = Question::create($questionData);

            return $question->load(['exam', 'options']);
        } catch (\Exception $e) {
            Log::error('Failed to create question: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateQuestion($questionId, $data, $user)
    {
        try {
            $question = Question::findOrFail($questionId);

            if (!$this->canManageQuestion($user, $question)) {
                throw new \Exception('Unauthorized to manage this question');
            }

            $questionData = $this->prepareQuestionData($data, $question->exam, $user, $question);
            Log::info('Prepared question data:', $questionData);

            $question->update($questionData);

            return $question->load(['exam', 'options']);
        } catch (\Exception $e) {
            Log::error('Failed to update question: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteQuestion($questionId, $user)
    {
        try {
            $question = Question::findOrFail($questionId);

            if (!$this->canManageQuestion($user, $question)) {
                throw new \Exception('Unauthorized to manage this question');
            }

            $question->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete question: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamQuestions($examId, $user = null, $includeAnswers = false)
    {
        try {
            $exam = Exam::findOrFail($examId);

            if ($user && !$exam->hasAccessFor($user)) {
                throw new \Exception('Access denied to exam questions');
            }

            $query = $exam->questions();

            if (!$includeAnswers) {
                $query->select([
                    'id', 'exam_id', 'title', 'slide', 'type', 'duration', 'points', 'options', 'files', 'q_options'
                ]);
            }

            return $query->orderBy('id')->get();
        } catch (\Exception $e) {
            Log::error('Failed to get exam questions: ' . $e->getMessage());
            throw $e;
        }
    }

    public function checkAnswer($questionId, $studentAnswer)
    {
        try {
            $question = Question::findOrFail($questionId);
            return $this->isAnswerCorrect($question, $studentAnswer);
        } catch (\Exception $e) {
            Log::error('Failed to check answer: ' . $e->getMessage());
            return false;
        }
    }

    public function validateQuestionData($data, $type)
    {
        $rules = [
            'title' => 'required|string|max:1000',
            'type' => 'required|in:multiple_choice,true_false,ordinary,sequence,slide',
            'duration' => 'nullable|integer|min:1|max:3600',
            'points' => 'nullable|integer|min:0|max:10000',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
        ];

        switch ($type) {
            case 'multiple_choice':
                $rules['options'] = 'required|array|min:2';
                $rules['options.*.text'] = 'required|string';
                $rules['options.*.correct'] = 'required|boolean';
                break;
            case 'true_false':
                $rules['correct_answer'] = 'required|in:true,false';
                break;
            case 'ordinary':
                $rules['short_answer'] = 'required|string';
                $rules['alternative_answers'] = 'nullable|array';
                break;
            case 'sequence':
                $rules['sequence_options'] = 'required|array|min:2';
                $rules['sequence_options.*.text'] = 'required|string';
                break;
        }

        return $rules;
    }

    private function prepareQuestionData($data, $exam, $user, $existingQuestion = null)
    {
        try {
            $questionData = [
                'exam_id' => $exam->id,
                'teacher_id' => $exam->teacher_id,
                'institute_id' => $exam->institute_id,
                'title' => $data['title'],
                'type' => $data['type'],
                'duration' => $data['duration'] ?? 30,
                'points' => $data['points'] ?? $data['point'] ?? 1,
                'difficulty_level' => $data['difficulty_level'] ?? 'medium',
                'slide' => $data['slide'] ?? null,
                'files' => $this->handleFileUploads($data, $existingQuestion),
                'q_options' => is_string($data['q_options']) ? $data['q_options'] : json_encode($data['q_options'] ?? []),
            ];
        } catch (\Exception $e) {
            Log::error('Error in basic question data preparation: ' . $e->getMessage());
            throw $e;
        }

        try {
            switch ($data['type']) {
                case 'multiple_choice':
                    $options = is_string($data['options']) ? json_decode($data['options'], true) : $data['options'];
                    $preparedOptions = $this->prepareMultipleChoiceOptions($options, $data);
                    $questionData['options'] = json_encode($preparedOptions);
                    break;

                case 'true_false':
                    $questionData['correct_answer'] = $data['correct_answer'];
                    $options = is_string($data['options']) ? json_decode($data['options'], true) : $data['options'];
                    $preparedOptions = $options ?: $this->prepareTrueFalseOptions($data['correct_answer']);
                    $preparedOptions = $this->prepareTrueFalseOptionsWithImages($preparedOptions, $data);
                    $questionData['options'] = json_encode($preparedOptions);
                    break;

                case 'ordinary':
                    $questionData['short_answer'] = $data['short_answer'];
                    $alternatives = is_string($data['alternative_answers']) ? json_decode($data['alternative_answers'], true) : $data['alternative_answers'];
                    $questionData['alternative_answers'] = $alternatives ?? [];
                    $questionData['case_sensitive'] = isset($data['case_sensitive']) ? (bool)$data['case_sensitive'] : false;
                    break;

                case 'sequence':
                    $sequenceData = $data['sequence_options'] ?? $data['options'] ?? null;
                    if (is_string($sequenceData)) {
                        $sequenceOptions = json_decode($sequenceData, true);
                    } elseif (is_array($sequenceData)) {
                        $sequenceOptions = $sequenceData;
                    } else {
                        $sequenceOptions = [];
                    }
                    $questionData['sequence_options'] = json_encode($sequenceOptions);
                    $questionData['options'] = json_encode($sequenceOptions);
                    $questionData['allow_partial_credit'] = isset($data['allow_partial_credit']) ? (bool)$data['allow_partial_credit'] : true;
                    break;
            }

            return $questionData;
        } catch (\Exception $e) {
            Log::error('Error in type-specific data preparation: ' . $e->getMessage());
            throw $e;
        }
    }

    private function prepareMultipleChoiceOptions($options, $data = null)
    {
        $preparedOptions = [];
        $optionFiles = isset($data['option_files']) && is_array($data['option_files']) ? $data['option_files'] : [];
        $fileIndex = 0;

        $uploadPath = public_path('uploads/options');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($options as $index => $option) {
            $preparedOption = [
                'id' => $option['id'] ?? 'opt_' . ($index + 1),
                'text' => $option['text'],
                'correct' => (bool)$option['correct'],
                'shape' => $option['shape'] ?? $this->getDefaultShape($index)
            ];

            $removeImageKey = "remove_option_image_{$index}";
            if ($data && isset($data[$removeImageKey]) && $data[$removeImageKey] == 'true') {
                if (isset($option['image']) && !empty($option['image'])) {
                    $oldImagePath = public_path(ltrim($option['image'], '/'));
                    if (file_exists($oldImagePath)) @unlink($oldImagePath);
                }
                $preparedOption['image'] = null;
            }
            elseif (!empty($optionFiles) && isset($optionFiles[$fileIndex]) && $optionFiles[$fileIndex]->isValid()) {
                $hasNewFile = isset($option['image']) && is_string($option['image']) && preg_match('/^option_.*_image\./', $option['image']);
                if ($hasNewFile) {
                    $file = $optionFiles[$fileIndex];
                    if (isset($option['image']) && !empty($option['image']) && !preg_match('/^option_.*_image\./', $option['image'])) {
                        $oldImagePath = public_path(ltrim($option['image'], '/'));
                        if (file_exists($oldImagePath)) @unlink($oldImagePath);
                    }
                    $filename = 'option_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $preparedOption['image'] = 'uploads/options/' . $filename;
                    $fileIndex++;
                }
            }
            elseif ($data && isset($data['uploaded_files']) && isset($data['uploaded_files']["option_image_{$index}"])) {
                $file = $data['uploaded_files']["option_image_{$index}"];
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    if (isset($option['image']) && !empty($option['image'])) {
                        $oldImagePath = public_path(ltrim($option['image'], '/'));
                        if (file_exists($oldImagePath)) @unlink($oldImagePath);
                    }
                    $filename = 'option_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $preparedOption['image'] = 'uploads/options/' . $filename;
                }
            } elseif (isset($option['image']) && !empty($option['image'])) {
                $preparedOption['image'] = $option['image'];
            } else {
                $preparedOption['image'] = null;
            }

            $preparedOptions[] = $preparedOption;
        }
        return $preparedOptions;
    }

    private function prepareTrueFalseOptions($correctAnswer)
    {
        return [
            ['id' => 'true', 'text' => 'True', 'correct' => $correctAnswer == 'true', 'shape' => 'triangle'],
            ['id' => 'false', 'text' => 'False', 'correct' => $correctAnswer == 'false', 'shape' => 'diamond']
        ];
    }

    private function prepareTrueFalseOptionsWithImages($options, $data = null)
    {
        $preparedOptions = [];
        $optionFiles = isset($data['option_files']) && is_array($data['option_files']) ? $data['option_files'] : [];
        $fileIndex = 0;

        $uploadPath = public_path('uploads/options');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($options as $index => $option) {
            $preparedOption = [
                'id' => $option['id'] ?? ($index == 0 ? 'true' : 'false'),
                'text' => $option['text'],
                'correct' => (bool)$option['correct'],
                'shape' => $option['shape'] ?? ($index == 0 ? 'triangle' : 'diamond')
            ];

            $removeImageKey = "remove_option_image_{$index}";
            if ($data && isset($data[$removeImageKey]) && $data[$removeImageKey] == 'true') {
                if (isset($option['image']) && !empty($option['image'])) {
                    $oldImagePath = public_path(ltrim($option['image'], '/'));
                    if (file_exists($oldImagePath)) @unlink($oldImagePath);
                }
                $preparedOption['image'] = null;
            }
            elseif (!empty($optionFiles) && isset($optionFiles[$fileIndex]) && $optionFiles[$fileIndex]->isValid()) {
                $hasNewFile = isset($option['image']) && is_string($option['image']) && preg_match('/^option_.*_image\./', $option['image']);
                if ($hasNewFile) {
                    $file = $optionFiles[$fileIndex];
                    $filename = 'tf_option_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $preparedOption['image'] = 'uploads/options/' . $filename;
                    $fileIndex++;
                }
            }
            elseif ($data && isset($data['uploaded_files']) && isset($data['uploaded_files']["option_image_{$index}"])) {
                $file = $data['uploaded_files']["option_image_{$index}"];
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $filename = 'tf_option_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $preparedOption['image'] = 'uploads/options/' . $filename;
                }
            } elseif (isset($option['image']) && !empty($option['image'])) {
                $preparedOption['image'] = $option['image'];
            } else {
                $preparedOption['image'] = null;
            }

            $preparedOptions[] = $preparedOption;
        }
        return $preparedOptions;
    }

    private function isAnswerCorrect($question, $studentAnswer)
    {
        $studentAnswerTrimmed = strtolower(trim((string)$studentAnswer));

        switch ($question->type) {
            case 'multiple_choice':
                $options = is_array($question->options) ? $question->options : json_decode($question->options, true) ?? [];
                foreach ($options as $option) {
                    if (($option['correct'] ?? false) && isset($option['text']) && strtolower(trim((string)$option['text'])) == $studentAnswerTrimmed) return true;
                }
                return false;

            case 'true_false':
                if (isset($question->correct_answer) && $question->correct_answer !== '') return $studentAnswerTrimmed == strtolower(trim((string)$question->correct_answer));
                $options = is_array($question->options) ? $question->options : json_decode($question->options, true) ?? [];
                foreach ($options as $option) {
                    if (($option['correct'] ?? false) && isset($option['text']) && strtolower(trim((string)$option['text'])) == $studentAnswerTrimmed) return true;
                }
                return false;

            case 'sequence':
                $correctSequence = !empty($question->sequence_options) ? (is_array($question->sequence_options) ? $question->sequence_options : json_decode($question->sequence_options, true)) : (is_array($question->options) ? $question->options : json_decode($question->options, true));
                $studentSequence = json_decode((string)$studentAnswer, true);
                if (!is_array($correctSequence) || !is_array($studentSequence) || count($correctSequence) !== count($studentSequence)) return false;
                for ($i = 0; $i < count($correctSequence); $i++) {
                    $correctText = is_array($correctSequence[$i]) ? ($correctSequence[$i]['text'] ?? $correctSequence[$i]) : $correctSequence[$i];
                    $studentText = is_array($studentSequence[$i]) ? ($studentSequence[$i]['text'] ?? $studentSequence[$i]) : $studentSequence[$i];
                    if (strtolower(trim((string)$studentText)) !== strtolower(trim((string)$correctText))) return false;
                }
                return true;

            case 'ordinary':
                $correctAnswer = strtolower(trim((string)($question->short_answer ?? '')));
                if (!empty($correctAnswer) && $studentAnswerTrimmed == $correctAnswer) return true;
                $alternatives = is_array($question->alternative_answers) ? $question->alternative_answers : json_decode($question->alternative_answers, true);
                if (is_array($alternatives)) {
                    foreach ($alternatives as $alt) {
                        if ($studentAnswerTrimmed == strtolower(trim((string)$alt))) return true;
                    }
                }
                return false;

            case 'slide':
                return true;
            default:
                return false;
        }
    }

    private function canManageExam($user, $exam)
    {
        if ($user instanceof \App\Models\Teacher && $exam->teacher_id == $user->id) return true;
        if ($user instanceof \App\Models\Institute && $exam->institute_id == $user->id) return true;
        return false;
    }

    private function canManageQuestion($user, $question)
    {
        return $this->canManageExam($user, $question->exam);
    }

    private function getDefaultShape($index)
    {
        $shapes = ['triangle', 'diamond', 'circle', 'rectangle', 'pentagon', 'hexagon'];
        return $shapes[$index % count($shapes)];
    }

    private function handleFileUploads($data, $existingQuestion = null)
    {
        try {
            if (isset($data['remove_main_image']) && $data['remove_main_image'] == 'true') {
                if ($existingQuestion && $existingQuestion->files) {
                    foreach ((array)$existingQuestion->files as $filename) {
                        if (Storage::disk('public')->exists('questions/' . $filename)) Storage::disk('public')->delete('questions/' . $filename);
                    }
                }
                return [];
            }

            $uploadedFiles = [];
            $hasNewFiles = false;

            if (isset($data['uploaded_files'])) {
                $files = $data['uploaded_files'];
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('questions', $filename, 'public');
                            $uploadedFiles[] = $filename;
                            $hasNewFiles = true;
                        }
                    }
                } elseif ($files instanceof \Illuminate\Http\UploadedFile) {
                    $filename = time() . '_' . uniqid() . '.' . $files->getClientOriginalExtension();
                    $files->storeAs('questions', $filename, 'public');
                    $uploadedFiles[] = $filename;
                    $hasNewFiles = true;
                }
            }

            foreach ($data as $key => $value) {
                if (preg_match('/^files\[\d+\]$/', $key) && $value instanceof \Illuminate\Http\UploadedFile) {
                    $filename = time() . '_' . uniqid() . '.' . $value->getClientOriginalExtension();
                    $value->storeAs('questions', $filename, 'public');
                    $uploadedFiles[] = $filename;
                    $hasNewFiles = true;
                }
            }

            if (!$hasNewFiles && $existingQuestion && $existingQuestion->files) {
                $existingFiles = is_string($existingQuestion->files) ? json_decode($existingQuestion->files, true) : $existingQuestion->files;
                if (is_array($existingFiles)) {
                    $flatFiles = [];
                    foreach ($existingFiles as $file) {
                        if (is_array($file)) $flatFiles = array_merge($flatFiles, $file);
                        else $flatFiles[] = $file;
                    }
                    return $flatFiles;
                }
                return [];
            }

            if ($hasNewFiles && $existingQuestion && $existingQuestion->files) {
                $existingFiles = is_string($existingQuestion->files) ? json_decode($existingQuestion->files, true) : $existingQuestion->files;
                if (is_array($existingFiles)) {
                    foreach ($existingFiles as $filename) {
                        if (is_array($filename)) {
                            foreach ($filename as $nested) {
                                if (Storage::disk('public')->exists('questions/' . $nested)) Storage::disk('public')->delete('questions/' . $nested);
                            }
                        } elseif (Storage::disk('public')->exists('questions/' . $filename)) {
                            Storage::disk('public')->delete('questions/' . $filename);
                        }
                    }
                }
            }

            return $uploadedFiles;
        } catch (\Exception $e) {
            Log::error('Error in handleFileUploads: ' . $e->getMessage());
            throw $e;
        }
    }
}