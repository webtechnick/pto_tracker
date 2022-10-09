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

    public function viewpto(User $user = null, Employee $employee)
    {
        // If user is registered and an admin.
        if ($user && $user->isAdmin()) {
            return true;
        }

        // If google session user is the employee
        $google = Session::get('GoogleUser');
        if ($google->name == $employee->name) {
            return true;
        }

        return false;
    }
}
