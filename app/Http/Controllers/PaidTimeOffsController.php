<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Http\Requests\PaidTimeOffRequest;
use App\Mail\PaidTimeOffRequested;
use App\PaidTimeOff;
use App\Tag;
use App\Traits\Flashes;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaidTimeOffsController extends Controller
{
    use Flashes;

    public function __construct()
    {
        $this->middleware(['auth', 'admin'])->only(['approve', 'deny', 'destroy']);
    }

    /**
     * Show the calendar
     *
     * @param  [type] $year [description]
     * @param  [type] $team [description]
     * @return [type]       [description]
     */
    public function home($year = null, $team = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $query = Employee::orderBy('name', 'ASC');
        if ($team) {
            $query->byInputTags($team);
        }
        $employees = $query->get();

        $teams = Tag::all();

        return view('pto.index', compact('employees', 'year', 'team', 'teams'));
    }

    /**
     * Submit PTO
     *
     * @param  PaidTimeOffRequest $request [description]
     * @return [type]                      [description]
     */
    public function store(PaidTimeOffRequest $request)
    {
        $pto = PaidTimeOff::saveForm($request->all());

        // Send Mail
        Mail::to(User::admins())->send(new PaidTimeOffRequested($pto));

        if ($request->ajax()) {
            return $pto;
        }

        $this->goodFlash('Paid Time Off Requested: ' . $pto->simpleString());

        return redirect()->route('home');
    }

    /**
     * Admin approve the PTO, protected by middleware
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function approve($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->approve()->save();
        return $pto;
    }

    /**
     * Admin deny the PTO, protected by middleware
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deny($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->deny()->save();
        return $pto;
    }

    /**
     * Delete the PTO, protected by middleware
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function destroy($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->delete();
        return 1;
    }

    /**
     * Admin Send PTO to OOO calendar, protected by middleware
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function sent_to_calendar($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->sentToCalendar()->save();
        return 1;
    }


    /**
     * Ajax load of PTOs
     *
     * @param  [type] $year [description]
     * @param  [type] $team [description]
     * @return [type]       [description]
     */
    public function get_ptos($year = null, $team = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $query = PaidTimeOff::whereYear('end_time', $year)
                           ->with(['employee' => function ($query) {
                                $query->select(['id', 'name', 'color', 'bgcolor']);
                            }]);
        if ($team) {
            // Add Team condition.
            $query->whereHas('employee', function($q) use ($team) {
                $q->byInputTags($team);
            });
        }

        $ptos = $query->get();
        return $ptos;
    }

    /**
     * Wrapper for pto@home
     *
     * @param  [type] $team [description]
     * @param  [type] $year [description]
     * @return [type]       [description]
     */
    public function team($team, $year = null)
    {
        return $this->home($year, $team);
    }

    /**
     * Wrapper for pto@get_ptos
     *
     * @param  [type] $team [description]
     * @param  [type] $year [description]
     * @return [type]       [description]
     */
    public function get_team_ptos($team, $year = null)
    {
        return $this->get_ptos($year, $team);
    }
}
