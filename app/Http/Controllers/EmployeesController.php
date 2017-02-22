<?php

namespace App\Http\Controllers;

use App\Employee;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index()
    {
        $employees = Employee::select([
            'id', 'name', 'color', 'bgcolor'
        ])->get();
        return $employees;
    }
}
