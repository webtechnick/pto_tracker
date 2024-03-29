<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Events\PaidTimeOffApproved;
use App\Events\PaidTimeOffDeleted;
use App\Events\PaidTimeOffRequested;
use App\Http\EmployeeSearch;
use App\Http\PaidTimeOffSearch;
use App\Http\Requests\PaidTimeOffRequest;
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
        $this->middleware(['auth', 'manager'])->only(['approve', 'deny', 'destroy']);
    }

    /**
     * Show the calendar
     *
     * @param  [type] $year [description]
     * @param  [type] $team [description]
     * @return [type]       [description]
     */
    public function home(Request $request, $year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $search = new EmployeeSearch($request);
        $employees = $search->get();

        // Pass team to view
        $team = $search->field('team');
        $selectedteam = Tag::byName($team)->first();
        $old = $search->old();

        return view('pto.index', compact('employees', 'year', 'selectedteam', 'old'));
    }

    /**
     * Submit PTO
     *
     * @param  PaidTimeOffRequest $request [description]
     * @return [type]                      [description]
     */
    public function store(PaidTimeOffRequest $request)
    {
        // Save PTO
        $pto = PaidTimeOff::saveForm($request->all());

        // Trigger Event.
        // This could be a PaidTimeOffCreated event, but I wanted to be explicit
        // that this event only gets triggered when a PTO is requested not
        // just created.
        event(new PaidTimeOffRequested($pto));

        if ($request->ajax()) {
            return $pto;
        }

        $this->goodFlash('Paid Time Off Requested: ' . $pto->simpleString());

        return redirect()->route('home');
    }

    /**
     * Admin/Manager approve the PTO, protected by middleware
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function approve($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);
        $pto->approve()->save();

        // Trigger Approved Event
        event(new PaidTimeOffApproved($pto));

        return $pto;
    }

    /**
     * Admin/Manager deny the PTO, protected by middleware
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
     * Admin/Manager Delete the PTO, protected by middleware
     *
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function destroy($id = null)
    {
        $pto = PaidTimeOff::findOrFail($id);

        // Trigger Deleted Event. This likely should be a model listener, but
        // following the pattern of other events being triggered in controller.
        event(new PaidTimeOffDeleted($pto));

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
    public function get_ptos(Request $request, $year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $request->merge(['year' => $year]);
        return (new PaidTimeOffSearch($request))->get();
    }
}
