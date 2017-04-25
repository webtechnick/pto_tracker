<?php

namespace App;

use App\PaidTimeOff;
use App\Traits\UtilityScopes;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use UtilityScopes;

    protected $fillable = ['name', 'title', 'color', 'bgcolor', 'phone'];

    protected $casts = [
        'is_on_call' => 'boolean'
    ];

    //protected $appends = ['pending_days_left', 'days_left'];

    /**
     * Employee has many Paid Time Off
     * @return [type] [description]
     */
    public function ptos()
    {
        return $this->hasMany(PaidTimeOff::class);
    }

    public function getPendingDaysLeftAttribute()
    {
        return $this->pendingDaysLeft();
    }

    public function getDaysLeftAttribute()
    {
        return $this->daysLeft();
    }

    public function scopeOnCall($query)
    {
        return $query->where('is_on_call', true);
    }

    public function isOnCall()
    {
        return $this->is_on_call;
    }

    public function setOnCall()
    {
        $this->is_on_call = true;
        return $this;
    }

    static public function clearOnCall()
    {
        Employee::where('is_on_call', true)->update(['is_on_call' => false]);
    }

    /**
     * Get all the PTO by the year
     * @param  [type] $year (this year by default)
     * @return collection of PaidTimeOff
     */
    public function ptosByYear($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }


        return $this->ptos()
               ->whereYear('start_time', '=', $year)
               ->whereYear('end_time', '=', $year)
               ->get();
    }

    /**
     * Get the days left of an employee by year
     * @return float days left to take
     */
    public function daysLeft($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        $days_taken = $this->ptos()
                ->select(['id', 'days'])
                ->whereYear('end_time', $year)
                ->approved()
                ->get()
                ->sum('days');

        return config('app.max_days_off') - $days_taken;
    }

    /**
     * Get the days left pending approval for an employee in a year
     * @return float days left to take
     */
    public function pendingDaysLeft($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        $days_taken = $this->ptos()
                ->select(['id', 'days'])
                ->whereYear('end_time', $year)
                ->get()
                ->sum('days');

        return config('app.max_days_off') - $days_taken;
    }

    /**
     * Add PTO to an employee
     * @param PaidTimeOff $pto [description]
     */
    public function addPto(PaidTimeOff $pto)
    {
        return $this->ptos()->save($pto);
    }
}
