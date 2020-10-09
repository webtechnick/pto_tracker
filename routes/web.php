<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Admin Routing
Route::group([
    'middleware' => ['auth', 'admin'],
    'as' => 'admin.',
    'prefix' => 'admin',
    'namespace' => 'Admin',
], function() {
    //Route::get('/', 'AdminsController@index')->name('admin.dashboard');
    Route::get('/ptos', 'AdminPaidTimeOffsController@index')->name('ptos');

    Route::get('/', 'AdminEmployeesController@index')->name('employees');

    Route::get('/employees/create', 'AdminEmployeesController@create')->name('employees.create');
    Route::get('/employees/{employee}/edit', 'AdminEmployeesController@edit')->name('employees.edit');
    Route::get('/employees/{employee}/delete', 'AdminEmployeesController@delete')->name('employees.delete');
    Route::post('/employees/store', 'AdminEmployeesController@store')->name('employees.store');
    Route::post('/employees/{employee}/update', 'AdminEmployeesController@update')->name('employees.update');
    Route::get('/employees/{employee}/destroy', 'AdminEmployeesController@destroy')->name('employees.destroy');

    Route::get('/employees/oncall/clear', 'AdminEmployeesController@clear_on_call')->name('employees.clearoncall');
    Route::get('/employees/oncall/set/{employee}', 'AdminEmployeesController@set_on_call')->name('employees.setoncall');

    // Teams
    Route::get('/teams', 'AdminTeamsController@index')->name('teams');
    Route::get('/teams/create', 'AdminTeamsController@create')->name('teams.create');
    Route::get('/teams/{tag}/edit', 'AdminTeamsController@edit')->name('teams.edit');
    Route::get('/teams/{tag}/delete', 'AdminTeamsController@delete')->name('teams.delete');
    Route::post('/teams/store', 'AdminTeamsController@store')->name('teams.store');
    Route::post('/teams/{tag}/update', 'AdminTeamsController@update')->name('teams.update');
    Route::get('/teams/{tag}/destroy', 'AdminTeamsController@destroy')->name('teams.destroy');
});

// App Routing
Route::get('/{year?}', 'PaidTimeOffsController@home')->name('home')->middleware('google')->where([
    'year' => '[0-9]{4}'
]);
Route::get('/is_admin', 'UtilController@is_admin')->name('is_admin');

Route::get('/oncall', 'EmployeesController@oncall')->name('oncall')->middleware('google');
Route::get('/get/employees', 'EmployeesController@index')->name('employee.index.ajax');

Route::get('/get/ptos/{year?}', 'PaidTimeOffsController@get_ptos')
    ->name('pto.index.ajax')
    ->where([
        'year' => '[0-9]{4}'
    ]);
Route::get('/get/holidays/{year?}', 'HolidaysController@index')->name('holidays.index.ajax')
  ->where([
    'year' => '[0-9]{4}'
  ]);

Route::post('/ptos/store', 'PaidTimeOffsController@store')->name('pto.store');
Route::post('/ptos/approve/{id}', 'PaidTimeOffsController@approve')->name('pto.approve')->middleware('admin');
Route::post('/ptos/deny/{id}', 'PaidTimeOffsController@deny')->name('pto.deny')->middleware('admin');
Route::post('/ptos/destroy/{id}', 'PaidTimeOffsController@destroy')->name('pto.destroy')->middleware('admin');
Route::post('/ptos/sent_to_calendar/{id}', 'PaidTimeOffsController@sent_to_calendar')->name('pto.sent_to_calendar')->middleware('admin');
Route::get('/ptos/{id}/view', 'PaidTimeOffsController@view')->name('pto.view');

// Team Routes
Route::get('/{team}/{year?}', 'PaidTimeOffsController@team')->name('team')->middleware('google')->where([
    'year' => '[0-9]{4}'
]);
Route::get('/get/ptos/{team}/{year?}', 'PaidTimeOffsController@get_team_ptos')
    ->name('pto.team.ajax')
    ->where([
        'year' => '[0-9]{4}'
    ]);

Auth::routes();

Route::get('/home', 'HomeController@index');

// Route::get('login/github', 'Auth\LoginController@redirectToProvider');
// Route::get('login/github/callback', 'Auth\LoginController@handleProviderCallback');

Route::get('login/google', 'Auth\LoginController@redirectToProvider');
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback');
