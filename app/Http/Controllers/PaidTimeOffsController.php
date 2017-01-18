<?php

namespace App\Http\Controllers;

use App\Employee;
use App\PaidTimeOff;
use Illuminate\Http\Request;

class PaidTimeOffsController extends Controller
{
    public function index($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        $ptos = PaidTimeOff::whereYear('end_time', $year)->get();
        $employees = Employee::all();
        return view('pto.index', compact('ptos', 'employees'));
    }

    public function store() {
        $this->validate(request(), [
            'start_time' => 'required',
            'end_time' => 'required',
            'employee' => 'required'
        ]);

        $pto = PaidTimeOff::create(request()->all());

        if (request()->ajax()) {
            return $pto;
        }

        return redirect()->route('pto.index');
    }
}
