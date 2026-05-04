<?php

namespace App\Http\Controllers\Api;

use App\Holiday;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HolidayRequest;

class HolidayController extends Controller
{
    public function index(HolidayRequest $request)
    {
        $holidays = Holiday::whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $totalDays = $holidays->sum(function ($holiday) {
            return $holiday->is_half_day ? 0.5 : 1;
        });

        $data = $holidays->map(function ($holiday) {
            return [
                'id'          => $holiday->id,
                'title'       => $holiday->title,
                'date'        => $holiday->date,
                'is_half_day' => $holiday->is_half_day,
                'days'        => $holiday->is_half_day ? 0.5 : 1,
            ];
        });

        return response()->json([
            'data' => $data->all(),
            'meta' => [
                'total_days'     => $totalDays,
                'total_holidays' => $holidays->count(),
            ],
        ]);
    }
}
