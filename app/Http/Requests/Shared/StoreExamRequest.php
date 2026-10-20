<?php

namespace App\Http\Requests\Shared;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends BaseRequest
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
            'category_id' => 'required|exists:categories,id',
            'cover' => 'nullable|image|max:2048',
            'duration' => 'nullable|integer|min:0',
            'monetize' => 'required|in:free,paid',
            'price' => 'nullable|numeric|min:0|required_if:monetize,paid',
            'access_mode' => 'required|in:public,private,invitation',
            'options' => 'nullable|json',
        ];
    }
}