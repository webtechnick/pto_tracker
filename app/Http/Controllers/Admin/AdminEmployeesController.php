<?php

namespace App\Http\Controllers\Admin;

use App\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Traits\Flashes;
use Illuminate\Http\Request;

class AdminEmployeesController extends Controller
{
    use Flashes;

    public function index()
    {
        return view('employees.index', ['employees' => Employee::all()]);
    }

    public function set_on_call(Employee $employee)
    {
        $employee->setOnCall()->save();
        $this->goodFlash($employee->name . ' set to on call.');

        return redirect()->route('admin.employees');
    }

    public function clear_on_call()
    {
        Employee::clearOnCall();
        $this->goodFlash('All employees cleared of on call status.');

        return redirect()->route('admin.employees');
    }

    public function create()
    {
        return view('employees.create');
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function store(EmployeeRequest $request)
    {
        $employee = Employee::create($request->all());

        $this->goodFlash($employee->name . ' Created.');

        return redirect()->route('admin.employees');
    }

    public function update(Request $request, Employee $employee)
    {
        $employee->fill($request->all());
        $employee->save();

        $this->goodFlash($employee->name . ' Updated.');

        return redirect()->route('admin.employees');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        $this->goodFlash('Employee Removed');

        return redirect()->route('admin.employees');
    }
}
