<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'title', 'date', 'is_half_day',
    ];

    protected $casts = [
        'is_half_day' => 'boolean',
    ];

    /**
     * Check if a date exists
     *
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
     * Check if a date exists
     * @param  date string or Carbon
     * @return boolean  is the date a current holiday?
     */
    public static function isHalfDayHoliday($date)
    {
        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }
        return self::where('date', $date->toDateString())
                   ->where('is_half_day', true)
                   ->exists();
    }

    /**
     * Is the holiday a half day
     *
     * @return boolean [description]
     */
    public function isHalfDay()
    {
        return !! $this->is_half_day;
    }

    /**
     * Scope Half Day
     *
     * @return [type] [description]
     */
    public function scopeHalfDay($query)
    {
        return $query->where('is_half_day', true);
    }

    /**
     * Scope Full Day
     *
     * @return [type] [description]
     */
    public function scopeFullDay($query)
    {
        return $query->where('is_half_day', false);
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

    /**
     * Bulk create holidays
     *
     * Example:

Holiday 1,2022-01-02
Holiday 2,2022-07-04
Holiday Half,2022-08-14,1
Holiday 4,2022-12-25
Holiday 5,2022-12-26

     *
     * @param  [type] $data [description]
     *
     * @return $count of holidays created
     */
    public static function bulkFromRequest($bulk)
    {
        $count = 0;
        $rows = explode(PHP_EOL,$bulk['bulk']);

        foreach ($rows as $row) {
            $parsed = str_getcsv($row);

            $half_day = false;
            if (isset($parsed[2])) {
                $half_day = !!trim($parsed[2]);
            }

            $data = [
                'title' => trim($parsed[0]),
                'date' => trim($parsed[1]),
                'is_half_day' => $half_day,
            ];

            self::createFromRequest($data);
            $count++;
        }

        return $count;
    }

    /**
     * Take in the date range and create half day holidays on every friday
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function bulkHalfFromRequest($data)
    {
        $start = Carbon::parse($data['start']);
        $end   = Carbon::parse($data['end']);
        $day = Carbon::parse($start);

        $count = 0;
        while ($day->timestamp <= $end->timestamp) {
            if ($day->isFriday() && !self::isHoliday($day)) {
                self::createFromRequest([
                    'title' => 'Summer Hours',
                    'date' => $day->toDateString(),
                    'is_half_day' => true
                ]);
                $count++;
            }

            $day->addDay();
        }

        return $count;
    }
}
