<?php

use App\Http\Controllers\Admin\AdminHolidaysController;
use App\Http\Controllers\Admin\AdminTeamsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\HolidaysController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Manager\ManagerEmployeesController;
use App\Http\Controllers\PaidTimeOffsController;
use App\Http\Controllers\UtilController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded within the "web" middleware group.
|
*/

// Manager Routing
Route::group([
    'middleware' => ['auth', 'manager'],
    'as' => 'manager.',
    'prefix' => 'manager',
], function () {
    // Employee
    Route::get('/', [ManagerEmployeesController::class, 'index'])->name('employees');
    Route::get('/employees/create', [ManagerEmployeesController::class, 'create'])->name('employees.create');
    Route::get('/employees/{employee}/edit', [ManagerEmployeesController::class, 'edit'])->name('employees.edit');
    Route::get('/employees/{employee}/delete', [ManagerEmployeesController::class, 'delete'])->name('employees.delete');
    Route::post('/employees/store', [ManagerEmployeesController::class, 'store'])->name('employees.store');
    Route::post('/employees/{employee}/update', [ManagerEmployeesController::class, 'update'])->name('employees.update');
    Route::get('/employees/{employee}/destroy', [ManagerEmployeesController::class, 'destroy'])->name('employees.destroy');
    Route::get('/employees/oncall/clear', [ManagerEmployeesController::class, 'clear_on_call'])->name('employees.clearoncall');
    Route::get('/employees/oncall/set/{employee}', [ManagerEmployeesController::class, 'set_on_call'])->name('employees.setoncall');
});

// Admin Routing
Route::group([
    'middleware' => ['auth', 'admin'],
    'as' => 'admin.',
    'prefix' => 'admin',
], function () {
    // User
    Route::get('/users', [AdminUsersController::class, 'index'])->name('users');
    Route::get('/users/create', [AdminUsersController::class, 'create'])->name('users.create');
    Route::get('/users/{user}/edit', [AdminUsersController::class, 'edit'])->name('users.edit');
    Route::get('/users/{user}/delete', [AdminUsersController::class, 'delete'])->name('users.delete');
    Route::post('/users/store', [AdminUsersController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/update', [AdminUsersController::class, 'update'])->name('users.update');
    Route::get('/users/{user}/destroy', [AdminUsersController::class, 'destroy'])->name('users.destroy');

    // Holiday
    Route::get('/holidays', [AdminHolidaysController::class, 'index'])->name('holidays');
    Route::get('/holidays/create', [AdminHolidaysController::class, 'create'])->name('holidays.create');
    Route::get('/holidays/bulk', [AdminHolidaysController::class, 'bulk'])->name('holidays.bulk');
    Route::post('/holidays/bulk', [AdminHolidaysController::class, 'bulk_store'])->name('holidays.bulkstore');
    Route::post('/holidays/bulk_half', [AdminHolidaysController::class, 'bulk_half_store'])->name('holidays.halfstore');
    Route::get('/holidays/{holiday}/edit', [AdminHolidaysController::class, 'edit'])->name('holidays.edit');
    Route::get('/holidays/{holiday}/delete', [AdminHolidaysController::class, 'delete'])->name('holidays.delete');
    Route::post('/holidays/store', [AdminHolidaysController::class, 'store'])->name('holidays.store');
    Route::post('/holidays/{holiday}/update', [AdminHolidaysController::class, 'update'])->name('holidays.update');

    // Teams
    Route::get('/teams', [AdminTeamsController::class, 'index'])->name('teams');
    Route::get('/teams/create', [AdminTeamsController::class, 'create'])->name('teams.create');
    Route::get('/teams/{tag}/edit', [AdminTeamsController::class, 'edit'])->name('teams.edit');
    Route::get('/teams/{tag}/delete', [AdminTeamsController::class, 'delete'])->name('teams.delete');
    Route::post('/teams/store', [AdminTeamsController::class, 'store'])->name('teams.store');
    Route::post('/teams/{tag}/update', [AdminTeamsController::class, 'update'])->name('teams.update');
    Route::get('/teams/{tag}/destroy', [AdminTeamsController::class, 'destroy'])->name('teams.destroy');
});

// Consider wrapping all routes in google middleware instead of just front-facing routes

// App Routing
Route::get('/{year?}', [PaidTimeOffsController::class, 'home'])->name('home')->middleware('google')->where([
    'year' => '[0-9]{4}'
]);
Route::get('/is_admin', [UtilController::class, 'is_admin'])->name('is_admin');

Route::get('/oncall', [EmployeesController::class, 'oncall'])->name('oncall')->middleware('google');
Route::get('/get/employees', [EmployeesController::class, 'index'])->name('employee.index.ajax');

Route::get('/get/ptos/{year?}', [PaidTimeOffsController::class, 'get_ptos'])
    ->name('pto.index.ajax')
    ->where([
        'year' => '[0-9]{4}'
    ]);
Route::get('/get/holidays/{year?}', [HolidaysController::class, 'index'])->name('holidays.index.ajax')
    ->where([
        'year' => '[0-9]{4}'
    ]);

Route::post('/ptos/store', [PaidTimeOffsController::class, 'store'])->name('pto.store');
Route::post('/ptos/approve/{id}', [PaidTimeOffsController::class, 'approve'])->name('pto.approve')->middleware('manager');
Route::post('/ptos/deny/{id}', [PaidTimeOffsController::class, 'deny'])->name('pto.deny')->middleware('manager');
Route::post('/ptos/destroy/{id}', [PaidTimeOffsController::class, 'destroy'])->name('pto.destroy')->middleware('manager');
Route::post('/ptos/owner-destroy/{id}', [PaidTimeOffsController::class, 'ownerDestroy'])->name('pto.owner.destroy')->middleware('auth');
Route::post('/ptos/sent_to_calendar/{id}', [PaidTimeOffsController::class, 'sent_to_calendar'])->name('pto.sent_to_calendar')->middleware('manager');
Route::get('/ptos/{id}/view', [PaidTimeOffsController::class, 'view'])->name('pto.view');

Auth::routes();

Route::get('/home', [HomeController::class, 'index']);

Route::get('login/google', [LoginController::class, 'redirectToProvider']);
Route::get('login/google/callback', [LoginController::class, 'handleProviderCallback']);
