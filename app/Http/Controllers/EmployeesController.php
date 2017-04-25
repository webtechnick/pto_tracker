<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Traits\Flashes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeesController extends Controller
{
    use Flashes;

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
}
