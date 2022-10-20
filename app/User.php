<?php

namespace App;

use App\Employee;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * User Roles
     *   A User's role defines it's permissions on the site.
     *
     * Admin: All permissions.
     * Planner: Can see PTO days remaining.
     * User: No permissions. (default)
     *
     * @var [type]
     */
    public static $roles = [
        'admin' => 'Admin',
        'manager' => 'Manager', // Can see/approve/delete direct report PTO, Add/update/create Employees
        'planner' => 'Planner', // Can see PTO remaining
        'user' => 'User', // Default
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id', // nullable optional
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * User belongs to Employee
     *
     * This relationship is entirely optional and controlled by admin.
     *
     * @return [type] [description]
     */
    public function employee()
    {
        return $this->belongsTo('App\Employee');
    }

    /**
     * Check if user is an admin.
     *
     * @return boolean [description]
     */
    public function isAdmin()
    {
        return $this->role == 'admin';
    }

    /**
     * Check if user is a planner
     *
     * @return boolean [description]
     */
    public function isPlanner()
    {
        return $this->role == 'planner';
    }

    /**
     * Check if user is a manager
     *
     * @return boolean [description]
     */
    public function isManager()
    {
        return $this->role == 'manager';
    }

    /**
     * Check if user is an employee
     *
     * @return boolean [description]
     */
    public function isEmployee()
    {
        return $this->role == 'user';
    }

    /**
     * Return if user is a manager or admin
     *
     * @return boolean [description]
     */
    public function isManagerOrAdmin()
    {
        return $this->isAdmin() || $this->isManager();
    }

    /**
     * Scope to get admins
     *
     * @return [type] [description]
     */
    public function scopeAdmins($query)
    {
        return $query->where('role','admin');
    }

    /**
     * Scope to get admins
     *
     * @return [type] [description]
     */
    public function scopePlanners($query)
    {
        return $query->where('role','planner');
    }

    /**
     * Scope to get admins
     *
     * @return [type] [description]
     */
    public function scopeManagers($query)
    {
        return $query->where('role','manager');
    }

    /**
     * Scope to return all admin or managers
     *
     * @param  [type] $query [description]
     * @return [type]        [description]
     */
    public function scopeAllManagers($query)
    {
        return $query->where(function ($q) {
            $q->orWhere('role', 'admin');
            $q->orWhere('role', 'manager');
        });
    }

    /**
     * Is the user the manager of this employee?
     *
     * @param  Employee $employee [description]
     * @return boolean            [description]
     */
    public function isManagerOf(Employee $employee)
    {
        return $this->id == $employee->manager_id;
    }

    /**
     * Create a User from the incomming admin request
     *
     * @return [type] [description]
     */
    public static function createFromRequest($data)
    {
        $data['employee_id'] = null;

        // If we have an employee that is not already claimed
        // by a user, assign it to this newly created user.
        $employee = Employee::select(['id','name'])
                            ->where('name', $data['name'])
                            ->first();
        if ($employee && !$employee->user) {
            $data['employee_id'] = $employee->id;
        }

        return self::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'role'        => $data['role'],
            'employee_id' => $data['employee_id'],
            'password'    => bcrypt($data['password']),
        ]);
    }

    /**
     * Update a User from an incomming request
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function updateFromRequest($data)
    {
        $this->update($data);

        return $this;
    }
}
