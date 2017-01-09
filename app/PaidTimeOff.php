<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaidTimeOff extends Model
{
    protected $fillable = [
        'start', 'end', 'description'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function($pto) {
            $pto->calculateDays();
        });
    }

    /**
     * Calculate the days of a PTO request.
     * @return [type] [description]
     */
    public function calculateDays()
    {
        return null;
        $seconds = strtotime($this->start_time) - strtotime($this->end_time);
        $seconds_in_days = 86400; // 60 * 60 * 24;
        $days = (int) ($seconds / $seconds_in_days);
        if ($days < 1) {
            $days = 1;
        }
        $this->days = $days;
    }
}
