<?php

namespace App\Http\Requests\Dorm;

use App\Support\CurrentSchool;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class DormCreate extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'name' => ['required', 'string', Rule::unique('dorms')->where('school_id', CurrentSchool::id())],
        ];
    }

}
