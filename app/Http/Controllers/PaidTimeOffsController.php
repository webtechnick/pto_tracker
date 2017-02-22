<?php

namespace App\Http\Controllers;

use App\Employee;
use App\PaidTimeOff;
use Illuminate\Http\Request;

class PaidTimeOffsController extends Controller
{
    public function home()
    {
        $employees = Employee::all();
        return view('pto.index', compact('employees'));
    }

    public function index($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        $ptos = PaidTimeOff::whereYear('end_time', $year)->get();
        if ($request->ajax()) {
            return $ptos;
        }
        $employees = Employee::all();
        return view('pto.index', compact('ptos', 'employees'));
    }

    public function store()
    {
        $this->validate(request(), [
            'start_time' => 'required',
            'end_time' => 'required',
            'employee_id' => 'required'
        ]);

        $pto = PaidTimeOff::saveForm(request()->all());

        if (request()->ajax()) {
            return $pto;
        }

        return redirect()->route('pto.index');
    }

    public function approve($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->approve()->save();
        return $pto;
    }

    public function deny($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->deny()->save();
        return $pto;
    }

    public function get_ptos($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $ptos = PaidTimeOff::whereYear('end_time', $year)->with(['employee' => function ($query) {
            $query->select(['id', 'name', 'color', 'bgcolor']);
        }])->get();
        return $ptos;
    }
}
