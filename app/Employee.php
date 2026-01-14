<?php

namespace App;

use App\Events\EmployeeDeleting;
use App\PaidTimeOff;
use App\Traits\Filterable;
use App\Traits\Taggable;
use App\Traits\UtilityScopes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Employee extends Model
{
    use UtilityScopes,
        Filterable,
        Taggable;

    protected $fillable = [
        'name',
        'title',
        'color',
        'bgcolor',
        'phone',
        'max_days_off',
        'manager_id',
    ];

    protected $casts = [
        'is_on_call' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($employee) {
            event(new EmployeeDeleting($employee));
        });
    }

    //protected $appends = ['pending_days_left', 'days_left'];
    protected $appends = [
        'can_manage'
    ];


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

    /**
     * Return a user object
     *
     * @return [type] [description]
     */
    public function manager()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Employee has a user (optional)
     *
     * @return [type] [description]
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get the Employee's Teams as a string
     *
     * This is an alias of tag_string as Teams are just Tags.
     *
     * @return [type] [description]
     */
    public function getTeamsAttribute()
    {
        return $this->tag_string;
    }

    /**
     * Set the Employee Teams as a string.
     *
     * This is an alias of the tag_string as Teams are just Tags.
     *
     * @param [type] $value [description]
     */
    public function setTeamsAttribute($value)
    {
        return $this->tag_string = $value;
    }

    /**
     * Get the pending days left as an attribute
     *
     * @return [type] [description]
     */
    public function getPendingDaysLeftAttribute()
    {
        return $this->pendingDaysLeft();
    }

    /**
     * Get the days left as an attibute
     *
     * @return [type] [description]
     */
    public function getDaysLeftAttribute()
    {
        return $this->daysLeft();
    }

    /**
     * Can the current user approve/deny/delete their PTO
     * This boolean will be appended to the employee which
     * will be passed to the PTO vue application.
     *
     * @return boolean user can approve/deny/delete $this->ptos
     */
    public function getCanManageAttribute()
    {
        $user = Auth::user(); // Get logged in user (or null if no user)

        // If user is registered and an admin or planner
        if ($user && $user->isAdmin()) {
            return true;
        }

        // If user is employee's manager
        if ($user && $user->isManagerOf($this)) {
            return true;
        }

        return false;
    }

    /**
     * Scope of on Call (deprecated)
     *
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeOnCall($query)
    {
        return $query->where('is_on_call', true);
    }

    /**
     * Boolean is on call (deprecated)
     *
     * @return boolean [description]
     */
    public function isOnCall()
    {
        return !!$this->is_on_call;
    }

    /**
     * Set the employee to oncall status (deprecated)
     */
    public function setOnCall()
    {
        $this->is_on_call = true;
        return $this;
    }

    /**
     * Clear all oncall status (deprecated)
     *
     * @return [type] [description]
     */
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

    /**
     * Decides if someone can remove a specific PTO
     *
     * An employee can remove their own PTO if:
     * - They are logged in and associated with this employee
     * - The PTO belongs to this employee
     * - The PTO start_time is in the future
     *
     * Admins and managers can still remove any PTO via the manager middleware.
     *
     * @param  PaidTimeOff $pto
     * @return boolean
     */
    public function canRemovePto(PaidTimeOff $pto)
    {
        $user = Auth::user();

        // Must be logged in
        if (!$user) {
            return false;
        }

        // User must be associated with this employee
        if (!$user->employee_id || (int) $user->employee_id !== (int) $this->id) {
            return false;
        }

        // PTO must belong to this employee
        if ((int) $pto->employee_id !== (int) $this->id) {
            return false;
        }

        // PTO must be in the future
        return $pto->isFuture();
    }

    /**
     * Decides if someone can view this employee's PTO
     *
     * Check if Authenticated User is admin, otherwise check Google
     * If they are the same name.
     *
     * NOTE: This (should) be a policy (see EmployeePolicy) However
     *       Laravel 5.4 cannot use policies without a user attached
     *       And we use google to authenticate instead of user
     * @return [type] [description]
     */
    public function canViewPto()
    {
        $user = Auth::user(); // Get logged in user (or null if no user)

        // If user is registered and an admin or planner
        if ($user && $user->isAdmin()) {
            return true;
        }

        // If user is employee's manager
        if ($user && $user->isManagerOf($this)) {
            return true;
        }

        // This user is employee! No need to check google
        if ($user && $user->isEmployee() && $user->employee_id) {
            return $this->id == $user->employee_id;
        }

        // If user is planner
        if ($user && $user->isPlanner()) {
            // If planner has teams, only view PTO of team members.
            if ($user->employee_id) { // faster database trick
                return $this->hasAnyTag($user->employee->teams);
            }
            // Otherwise default true, planner may not track PTO with this app.
            return true;
        }

        // If google session user is the employee
        $google = Session::get('GoogleUser');
        if ($google && $google->name == $this->name) {
            return true;
        }

        // Default state
        return false;
    }

    /**
     * Employee has at least one PTO day on a specific date
     *
     * @param  DateString|Carbon  $date
     * @return boolean            PaidTimeOff exists for that day for this employee
     */
    public function hasPTOon($date)
    {
        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }

        return $this->ptos()
                    ->where('start_time', '<=', $date->toDateTimeString())
                    ->where('end_time', '>=', $date->toDateTimeString())
                    ->exists();
    }
}
