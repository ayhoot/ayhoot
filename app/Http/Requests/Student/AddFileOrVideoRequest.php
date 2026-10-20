<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class AddFileOrVideoRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

   
    public function rules(): array
    {
        return [
            'type' => 'required|in:file,video',
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|max:102400',
            'video_url' => 'nullable'
        ];
    }
}
