<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends BaseRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'monetize' => 'nullable|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'institute_id' => 'nullable|exists:institutes,id',
            'category_id' => 'nullable|exists:categories,id',
            'folder_id' => 'nullable|exists:folders,id'
        ];
    }
}
