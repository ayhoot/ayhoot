<?php

namespace App\Http\Requests\Shared;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends BaseRequest
{
    public function authorize()
    {
       return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'cover' => 'nullable|max:4048',
            'duration' => 'nullable|integer|min:0',
            'monetize' => 'sometimes|required|in:free,paid',
            'price' => 'nullable|numeric|min:0|required_if:monetize,paid',
            'status' => 'nullable|in:published,pending,rejected',
            'access_mode' => 'nullable|in:public,private,invitation',
            'institute_id' => 'nullable|exists:institutes,id',
            'folder_id' => 'nullable|exists:folders,id',
            'options' => 'nullable|json',
            'play_music' => 'nullable|boolean',
        ];
    }
}