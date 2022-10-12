<?php

namespace App\Http\Controllers\Manager;

use App\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Traits\Flashes;
use Illuminate\Http\Request;

class ManagerEmployeesController extends Controller
{
    use Flashes;

    /**
     * Show list of employees
     *
     * @return view
     */
    public function index()
    {
        $employees = Employee::orderBy('name', 'ASC')->get();

        return view('employees.index', compact('employees'));
    }

    /**
     * Set an employee on call
     *
     * @param Employee $employee [description]
     * @return redirect
     */
    public function set_on_call(Employee $employee)
    {
        $employee->setOnCall()->save();
        $this->goodFlash($employee->name . ' set to on call.');

        return redirect()->route('manager.employees');
    }

    /**
     * Clear all the Employees on call
     *
     * @return redirect
     */
    public function clear_on_call()
    {
        Employee::clearOnCall();
        $this->goodFlash('All employees cleared of on call status.');

        return redirect()->route('manager.employees');
    }

    /**
     * Show form to create an employee
     *
     * @return view
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Show a form to edit an employee
     *
     * @param  Employee $employee [description]
     * @return view
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Delete an Employee and all their PTO
     *
     * @param  Employee $employee [description]
     * @return redirect
     */
    public function delete(Employee $employee)
    {
        $employee->delete();

        $this->goodFlash('Employee and all related PTO removed.');

        return redirect()->route('manager.employees');
    }

    /**
     * Store a new employee
     *
     * @param  EmployeeRequest $request [description]
     * @return redirect
     */
    public function store(EmployeeRequest $request)
    {
        $employee = Employee::createFromRequest($request->all());

        $this->goodFlash($employee->name . ' Created.');

        return redirect()->route('manager.employees');
    }

    /**
     * Update an employee
     *
     * @param  Request  $request  [description]
     * @param  Employee $employee [description]
     * @return redirect
     */
    public function update(Request $request, Employee $employee)
    {
        $employee->updateFromRequest($request->all());

        $this->goodFlash($employee->name . ' Updated.');

        return redirect()->route('manager.employees');
    }
}
