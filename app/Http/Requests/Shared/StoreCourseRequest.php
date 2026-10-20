<?php

namespace App\Http\Requests\Shared;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'institute_id' => 'nullable|exists:institutes,id',
            'monetize' => 'nullable|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'cover' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}