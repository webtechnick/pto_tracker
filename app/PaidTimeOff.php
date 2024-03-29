<?php

namespace App;

use App\Employee;
use App\Holiday;
use App\Traits\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaidTimeOff extends Model
{
    use Filterable;

    protected $fillable = [
        'employee_id', 'start_time', 'end_time', 'description', 'notes',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_half_day' => 'boolean',
        'is_sent_to_calendar' => 'boolean',
    ];

    public function getFilters()
    {
        return [
            'employee.name'
        ];
    }

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

    /**
     * Save PTO from request
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function saveForm($data)
    {
        $pto = new PaidTimeOff($data);
        $pto->start_time = Carbon::parse($pto->start_time)->toDateString();
        $pto->end_time = Carbon::parse($pto->end_time)->toDateString();

        if (isset($data['half_day'])) {
            $pto->makeHalfDay();
        }

        $pto->save();
        return $pto;
    }

    /**
     * Approved scope
     *
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Pending scope
     *
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Is the PTO approved?
     *
     * @return boolean [description]
     */
    public function isApproved()
    {
        return !!$this->is_approved;
    }

    /**
     * Is the PTO pending?
     *
     * @return boolean [description]
     */
    public function isPending()
    {
        return !$this->isApproved();
    }

    /**
     * Paid Time Off is in the past
     * Carbon\Carbon wrapper
     *
     * @return boolean [description]
     */
    public function isPast()
    {
        return $this->end_time->isPast();
    }

    /**
     * PTO is in the future
     *
     * @return boolean [description]
     */
    public function isFuture()
    {
        return $this->start_time->isFuture();
    }

    /**
     * PTO is active now
     *
     * @return boolean [description]
     */
    public function isCurrent()
    {
        return $this->start_time->isPast() && $this->end_time->isFuture();
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
            // If it's a weekend, don't add a day and move onto next day
            if ($current_day->isWeekend()) {
                $current_day->addDay();
                continue;
            }

            // If it's a half day holiday, add a half day and move onto next day
            if (Holiday::isHalfDayHoliday($current_day)) {
                $this->days += .5;
                $current_day->addDay();
                continue;
            }

            // If it's a holiday, don't add a day, and move onto next day
            if (Holiday::isHoliday($current_day)) {
                $current_day->addDay();
                continue;
            }

            // If we're here, add the day/half-day and move onto next day
            if ($this->is_half_day) {
                $this->days += .5;
            } else {
                $this->days += 1;
            }
            $current_day->addDay();
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

    /**
     * Set pto to half day.
     * @return [type] [description]
     */
    public function makeHalfDay()
    {
        $this->end_time = $this->start_time;
        $this->is_half_day = true;
        $this->days = .5;
        return $this;
    }

    /**
     * Mark the PTO as sent to Calendar
     * @return [type] [description]
     */
    public function sentToCalendar()
    {
        $this->is_sent_to_calendar = true;
        return $this;
    }

    /**
     * Represent the PTO as a string
     * @return [type] [description]
     */
    public function simpleString()
    {
        $format = 'D, M jS Y';
        $approvedText = $this->approvedText();
        return $this->start_time->format($format) .
                ' to ' .
                $this->end_time->format($format) .
                ' by ' .
                $this->employee->name .
                ' (' . $approvedText . ') ' .
                $this->days .
                ' day(s).';
    }

    /**
     * Get the approved text
     *
     * @return [type] [description]
     */
    public function approvedText()
    {
        return $this->isApproved() ? 'Approved' : 'Pending';
    }

    /**
     * Represent the PTO as a subject line
     *
     * @param  boolean: will append current status to end.
     * @return [type] [description]
     */
    public function simpleSubject($status = true)
    {
        $approvedText = $status ? ' ' . $this->approvedText() : '';
        return 'Paid Time Off starting ' . $this->start_time->toFormattedDateString() . ' for ' . $this->days . ' days(s)' . $approvedText;
    }
}
