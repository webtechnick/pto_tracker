<?php

namespace App\Http\Requests;

use App\Traits\Flashes;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PaidTimeOffRequest extends FormRequest
{
    use Flashes;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        return [
            'start_time' => 'required',
            'end_time' => 'required|date|after_or_equal:start_time',
            'employee_id' => 'required'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->isSameYear()) {
                $validator->errors()->add('end_time', 'Error: Start and end date must be in the same year.');
            }
        });

        if ($validator->fails()) {
            $this->badFlash('Unable to save PTO request.');
        }
    }

    public function isSameYear()
    {
        $start_time = Carbon::parse($this->input('start_time'));
        $end_time = Carbon::parse($this->input('end_time'));
        return $start_time->year == $end_time->year;
    }
}
