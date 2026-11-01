<?php

namespace App\Http\Requests\TimeTable;

use App\Support\CurrentSchool;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class TTRecordRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->method() === 'POST'){
            return [
                'name' => ['required', 'string', 'min:3', Rule::unique('time_table_records')->where('school_id', CurrentSchool::id())],
                'my_class_id' => 'required',
            ];
        }

        return [
            'name' => ['required', 'string', 'min:3', Rule::unique('time_table_records')->where('school_id', CurrentSchool::id())->ignore($this->ttr)],
            'my_class_id' => 'required',
        ];
    }

    public function attributes()
    {
        return  [
            'my_class_id' => 'Class',
        ];
    }

}
