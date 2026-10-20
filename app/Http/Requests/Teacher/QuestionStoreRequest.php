<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class QuestionStoreRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_id' => 'required|exists:exams,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'institute_id' => 'nullable|exists:institutes,id',
            'title' => 'required',
            'type' => 'required|in:true_false,multiple_choice,ordinary,sequence,textarea,slide',
            'duration' => 'nullable|integer',
            'points' => 'nullable|integer',
            'correct_answer' => 'nullable|string',
            'options' => 'nullable',
            'short_answer' => 'nullable|string',
            'alternative_answers' => 'nullable',
            'sequence_options' => 'nullable',
            'allow_partial_credit' => 'boolean',
            'long_answer' => 'nullable|string',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'category_id' => 'nullable',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf,docx,mp4|max:10240',
            'q_options' => 'nullable'

        ];
    }
}