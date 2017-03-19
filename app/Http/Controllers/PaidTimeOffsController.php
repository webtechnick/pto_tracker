<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Http\Requests\PaidTimeOffRequest;
use App\Mail\PaidTimeOffRequested;
use App\PaidTimeOff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaidTimeOffsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin'])->only(['approve', 'deny', 'destroy']);
    }

    public function home($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        $employees = Employee::all();
        return view('pto.index', compact('employees', 'year'));
    }

    public function store(PaidTimeOffRequest $request)
    {
        $pto = PaidTimeOff::saveForm($request->all());

        // Send Mail
        Mail::to(User::admins())->send(new PaidTimeOffRequested($pto));

        if ($request->ajax()) {
            return $pto;
        }

        return redirect()->route('home');
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

    public function destroy($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->delete();
        return 1;
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
