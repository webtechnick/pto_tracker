<?php

namespace App;

use App\Employee;
use App\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaidTimeOff extends Model
{
    protected $fillable = [
        'employee_id', 'start_time', 'end_time', 'description', 'notes'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_half_day' => 'boolean'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($pto) {
            $pto->calculateDays();
        });

        static::updating(function ($pto) {
            $pto->calculateDays();
        });
    }

    public static function saveForm($data)
    {
        $pto = new PaidTimeOff($data);
        $pto->start_time = Carbon::parse($pto->start_time)->toDateString();
        $pto->end_time = Carbon::parse($pto->end_time)->toDateString();
        $pto->save();
        return $pto;
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * PaidTimeOff belongs to an employee
     * @return [type] [description]
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate the days of a PTO request.
     * @return [type] [description]
     */
    public function calculateDays()
    {
        if (!($this->start_time instanceof Carbon)) {
            $this->start_time = Carbon::parse($this->start_time);
        }
        if (!($this->end_time instanceof Carbon)) {
            $this->end_time = Carbon::parse($this->end_time);
        }

        // Walk through the PTO requested
        // Check for Holidays and weekends
        $current_day = Carbon::parse($this->start_time);
        $this->days = 0;
        while ($current_day->timestamp <= $this->end_time->timestamp) {
            if (!$current_day->isWeekend() && !Holiday::isHoliday($current_day)) {
                $this->days += 1;
            }
            $current_day->addDay();
        }

        // Handle special cases
        if ($this->days <= 1) {
            if ($this->is_half_day) {
                $this->days = .5;
            } else {
                $this->days = 1;
            }
        }

        return $this;
    }

    /**
     * Approve the PTO
     * @return self
     */
    public function approve()
    {
        $this->is_approved = true;
        return $this;
    }

    /**
     * Deny the PTO
     * @return self
     */
    public function deny()
    {
        $this->is_approved = false;
        return $this;
    }
}
