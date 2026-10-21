<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\TimeOffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded within the "api" middleware group and are
| prefixed with /api.
|
*/

Route::middleware('auth.api-token')->group(function () {
    Route::get('/time-off', [TimeOffController::class, 'index']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/holidays', [HolidayController::class, 'index']);
});
