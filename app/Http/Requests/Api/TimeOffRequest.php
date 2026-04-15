<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TimeOffRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'employee_names'   => 'sometimes|array',
            'employee_names.*' => 'string',
            'employee_ids'     => 'sometimes|array',
            'employee_ids.*'   => 'integer',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}
