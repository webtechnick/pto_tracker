<?php

namespace App;

use App\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaidTimeOff extends Model
{
    protected $fillable = [
        'start_time', 'end_time', 'description'
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

        $this->days = $this->start_time->diffInDays($this->end_time);
        // Handle special cases
        if ($this->days < 1) {
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
    public function approve() {
        $this->is_approved = true;
        return $this;
    }

    /**
     * Deny the PTO
     * @return self
     */
    public function deny() {
        $this->is_approved = false;
        return $this;
    }
}
