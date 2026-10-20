<?php

namespace App\Http\Requests\Shared;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends BaseRequest
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
            'price' => 'nullable|numeric|min:0',
            'monetize' => 'nullable|in:free,paid',
            'status' => 'nullable|in:published,draft',
            'cover' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}