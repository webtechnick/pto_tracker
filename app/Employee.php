<?php

namespace App;

use App\Events\EmployeeDeleting;
use App\PaidTimeOff;
use App\Traits\Filterable;
use App\Traits\Taggable;
use App\Traits\UtilityScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use UtilityScopes,
        Filterable,
        Taggable;

    protected $fillable = ['name', 'title', 'color', 'bgcolor', 'phone', 'max_days_off'];

    protected $events = [
        'deleting' => EmployeeDeleting::class,
    ];

    protected $casts = [
        'is_on_call' => 'boolean'
    ];

    //protected $appends = ['pending_days_left', 'days_left'];


    public function getFilters()
    {
        return [
            'name', 'title'
        ];
    }

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

        $max_days_off = $this->max_days_off ?: config('app.max_days_off');

        return $max_days_off - $days_taken;
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

        $max_days_off = $this->max_days_off ?: config('app.max_days_off');

        return $max_days_off - $days_taken;
    }

    /**
     * Add PTO to an employee
     * @param PaidTimeOff $pto [description]
     */
    public function addPto(PaidTimeOff $pto)
    {
        return $this->ptos()->save($pto);
    }

    /**
     * Create a Employee from the incomming request
     *
     * @return [type] [description]
     */
    public static function createFromRequest($data)
    {
        $employee = new self($data);

        DB::transaction(function() use ($employee, $data) {
            $employee->save(); // Concrete employee now

            if (!empty($data['tag_string'])) {
                $employee->syncTagString($data['tag_string']);
            }
        });


        return $employee;
    }

    /**
     * Update a Employee from an incomming request
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function updateFromRequest($data)
    {
        $this->update($data);

        if (!empty($data['tag_string'])) {
            $this->syncTagString($data['tag_string']);
        }

        return $this;
    }
}
