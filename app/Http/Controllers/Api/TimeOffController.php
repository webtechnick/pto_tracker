<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TimeOffRequest;
use App\PaidTimeOff;

class TimeOffController extends Controller
{
    public function index(TimeOffRequest $request)
    {
        $query = PaidTimeOff::with('employee')
            ->approved()
            ->where('start_time', '<=', $request->end_date)
            ->where('end_time', '>=', $request->start_date);

        if ($request->filled('employee_ids')) {
            $query->whereIn('employee_id', $request->employee_ids);
        }

        if ($request->filled('employee_names')) {
            $names = array_map('strtolower', $request->employee_names);
            $query->whereHas('employee', function ($q) use ($names) {
                $q->where(function ($inner) use ($names) {
                    foreach ($names as $name) {
                        $inner->orWhereRaw('LOWER(name) = ?', [$name]);
                    }
                });
            });
        }

        $entries = $query->get();

        $grouped = $entries->groupBy('employee_id')->map(function ($items) {
            $employee = $items->first()->employee;

            return [
                'employee_id'   => $employee->id,
                'employee_name' => $employee->name,
                'pto_days'      => $items->sum('days'),
                'entries'       => $items->map(function ($pto) {
                    return [
                        'start_time'  => $pto->start_time->toDateString(),
                        'end_time'    => $pto->end_time->toDateString(),
                        'days'        => $pto->days,
                        'is_half_day' => $pto->is_half_day,
                        'is_approved' => $pto->is_approved,
                        'description' => $pto->description,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json(['data' => $grouped->values()->all()]);
    }
}
