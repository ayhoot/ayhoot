<?php

namespace App\Http\Requests\Institute;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CourseContentRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
    }

   
    public function rules(): array
    {
        return [
            'content_index' => 'required|integer|min:0',
            'title' => 'required|string|max:255',
            'type' => 'required|in:file,video',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,mov,avi|max:102400',
            'video_url' => 'nullable|max:2048',
        ];
    }
}

