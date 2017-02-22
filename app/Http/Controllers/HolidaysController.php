<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HolidaysController extends Controller
{
    public function index($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        $holidays = PaidTimeOff::whereYear('date', $year)->get();
        return $holidays;
    }
}
