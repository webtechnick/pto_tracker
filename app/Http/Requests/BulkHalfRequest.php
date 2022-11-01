<?php

namespace App\Http\Requests;

use App\Traits\Flashes;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkHalfRequest extends FormRequest
{
    use Flashes;

    public $carbonStart;
    public $carbonEnd;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    /**
     * Overwrite message
     * @return [type] [description]
     */
    public function messages()
    {
        return [
            'end.after_or_equal' => 'End date must be later or equal to start date',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ];
    }

    /**
     * Set flash on session if we don't pass validation.
     * Check to make sure year is not the same.
     * @param  Validator $validator
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Must be same year
            if (!$this->isSameYear()) {
                $validator->errors()->add('end', 'Error: Start and End must be in the same year.');
            }
        });

        if ($validator->fails()) {
            $this->badFlash('Unable to save PTO request.');
        }
    }

    /**
     * Setup carbon time so we don't have to parse carbon twice.
     *
     * @return [type] [description]
     */
    private function setupCarbonTime()
    {
        $this->carbonStart = $this->carbonStart ?: Carbon::parse($this->input('start'));
        $this->carbonEnd   = $this->carbonEnd   ?: Carbon::parse($this->input('end'));
    }

    /**
     * Private function to check if start_time and end_time are the same year
     * @return boolean success
     */
    private function isSameYear()
    {
        $this->setupCarbonTime();
        return $this->carbonStart->year == $this->carbonEnd->year;
    }
}
