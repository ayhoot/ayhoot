<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthSignUpRequest extends BaseRequest
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
            'role' => 'required|string|in:student,teacher,institute',
            'username' => 'required|string|unique:students,username|unique:teachers,username|unique:institutes,username',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
            'city_id' => 'nullable|exists:cities,id'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->email && !$this->phone) {
                $validator->errors()->add('identifier', 'Either email or phone number is required.');
            }
        });
    }
}
