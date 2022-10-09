<?php

namespace App\Policies;

use App\User;
use App\Employee;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * This would be awesome to be able to use this
     * Unforunately Laravel 5.4 doesn't allow null users
     * to be able to be defined in any policy
     *
     * See App\Employee::canViewPto
     *
     * @param  User|null $user     [description]
     * @param  Employee  $employee [description]
     * @return [type]              [description]
     */
    public function viewpto(User $user = null, Employee $employee)
    {
        return $employee->canViewPto();
    }
}
