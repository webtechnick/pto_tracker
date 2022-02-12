<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'title', 'date'
    ];

    /**
     * Check if a date exists
     * @param  date string or Carbon
     * @return boolean  is the date a current holiday?
     */
    public static function isHoliday($date)
    {
        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }
        return self::where('date', $date->toDateString())->exists();
    }

    /**
     * Create a Holiday from the incomming request
     *
     * @return [type] [description]
     */
    public static function createFromRequest($data)
    {
        $holiday = new self($data);
        $holiday->date = Carbon::parse($holiday->date)->toDateString();
        $holiday->save();

        return $holiday;
    }

    /**
     * Update a Holiday from the incomming request
     *
     * @return [type] [description]
     */
    public function updateFromRequest($data)
    {
        $this->update($data);

        return $this;
    }
}
