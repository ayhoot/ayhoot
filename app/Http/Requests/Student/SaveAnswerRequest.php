<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class SaveAnswerRequest extends BaseRequest
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
            'game_id' => 'nullable|exists:games,id',
            'attempt_id' => 'nullable|exists:answers,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required',
            'answers.*.answer' => 'nullable',
            'answers.*.files' => 'nullable|array',
            'answers.*.files.*' => 'file|max:10240', // 10MB max
            'is_live_session' => 'nullable|boolean',
            'live_session_id' => 'nullable|exists:live_sessions,id',
        ];
    }
}
