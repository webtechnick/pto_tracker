<?php

namespace App\Http\Controllers\Admin;

use App\Holiday;
use App\Http\Controllers\Controller;
use App\Http\Requests\HolidayRequest;
use App\Traits\Flashes;
use Illuminate\Http\Request;

class AdminHolidaysController extends Controller
{
    use Flashes;

    /**
     * Show list of holidays
     *
     * @return view
     */
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'DESC')->get();

        return view('holidays.index', compact('holidays'));
    }

    /**
     * Show form to create an holiday
     *
     * @return view
     */
    public function create()
    {
        return view('holidays.create');
    }

    /**
     * Show a form to edit an holiday
     *
     * @param  holiday $holiday [description]
     * @return view
     */
    public function edit(holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    /**
     * Delete an holiday and all their PTO
     *
     * @param  holiday $holiday [description]
     * @return redirect
     */
    public function delete(Holiday $holiday)
    {
        $holiday->delete();

        $this->goodFlash('Holiday removed.');

        return redirect()->route('admin.holidays');
    }

    /**
     * Store a new holiday
     *
     * @param  holidayRequest $request [description]
     * @return redirect
     */
    public function store(HolidayRequest $request)
    {
        $holiday = Holiday::createFromRequest($request->all());

        $this->goodFlash($holiday->name . ' Created.');

        return redirect()->route('admin.holidays');
    }

    /**
     * Update an holiday
     *
     * @param  Request  $request  [description]
     * @param  holiday $holiday [description]
     * @return redirect
     */
    public function update(Request $request, Holiday $holiday)
    {
        $holiday->updateFromRequest($request->all());

        $this->goodFlash($holiday->name . ' Updated.');

        return redirect()->route('admin.holidays');
    }

    /**
     * Show the bulk add feature
     *
     * @return [type] [description]
     */
    public function bulk()
    {
        return view('holidays.bulk');
    }

    /**
     * Store the bulk holidays
     *
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function bulk_store(Request $request)
    {
        $count = Holiday::bulkFromRequest($request->all());

        $this->goodFlash("$count Holiday(s) bulk added.");

        return redirect()->route('admin.holidays');
    }
}
