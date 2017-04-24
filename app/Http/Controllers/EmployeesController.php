<?php

namespace App\Http\Controllers;

use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeesController extends Controller
{
    public function index()
    {
        $employees = Employee::select([
            'id', 'name', 'color', 'bgcolor'
        ])->get();
        return $employees;
    }

    public function oncall()
    {
        $onCallEmployees = Employee::onCall()->get();

        return view('employees.oncall', compact('onCallEmployees'));
    }

    public function set_on_call(Employee $employee)
    {
        $employee->setOnCall()->save();

        return back();
    }

    public function clear_on_call()
    {
        Employee::clearOnCall();

        return back();
    }
}
