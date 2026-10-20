<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class QuestionUpdateRequest extends BaseRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'exam_id' => 'nullable|exists:exams,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'institute_id' => 'nullable|exists:institutes,id',
            'title' => 'required|string|max:255',
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
            'files' => 'array'

        ];
    }
}
