<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ResetPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Ensure the user is authorized to make this request
    }

    public function rules()
    {
        return [
            'email' => 'required|string'
        ];
    }
} 