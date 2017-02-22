<?php

namespace App\Http\Controllers;

use App\Holiday;
use Illuminate\Http\Request;

class HolidaysController extends Controller
{
    public function index($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        $holidays = Holiday::whereYear('date', $year)->get();
        return $holidays;
    }
}
