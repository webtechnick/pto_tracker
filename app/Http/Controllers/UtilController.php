<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilController extends Controller
{
    public function is_admin()
    {
        if (!request()->user()) {
            return 0;
        }
        if (request()->user()->isAdmin()) {
            return 1;
        }
        return 0;
    }
}
