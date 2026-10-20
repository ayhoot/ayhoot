<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteStudentRequest extends BaseRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
             'student_id'=>'required',
            'type'=>'required',
            'invite_code'=>'required'
        ];
    }
}
