<?php

use Illuminate\Http\Request;

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
    'as' => 'admin::',
    'prefix' => 'admin',
    'namespace' => 'Admin',
], function() {
    Route::get('/', 'AdminPaidTimeOffsController@index')->name('dashboard');
    Route::get('/ptos/{id}/edit', 'AdminPaidTimeOffsController@edit')->name('pto.edit');
});

Route::get('/', 'PaidTimeOffsController@home')->name('home');
Route::get('/is_admin', function() {
    if (!request()->user()) {
        return 0;
    }
    if (request()->user()->isAdmin()) {
        return 1;
    }
});

Route::get('/{year?}', 'PaidTimeOffsController@index')
    ->name('pto.index')
    ->where([
        'year' => '[0-9]{4}'
    ]);
Route::get('/get/ptos/{year?}', 'PaidTimeOffsController@get_ptos')
    ->name('pto.index.ajax')
    ->where([
        'year' => '[0-9]{4}'
    ]);
Route::get('/get/holidays/{year?}', 'HolidaysController@index')->name('holidays.index.ajax')
  ->where([
    'year' => '[0-9]{4}'
  ]);

Route::get('/get/employees', 'EmployeesController@index')->name('employee.index.ajax');

Route::post('/ptos/store', 'PaidTimeOffsController@store')->name('pto.store');
Route::post('/ptos/approve/{id}', 'PaidTimeOffsController@approve')->name('pto.approve');
Route::post('/ptos/deny/{id}', 'PaidTimeOffsController@deny')->name('pto.deny');
Route::get('/ptos/{id}/view', 'PaidTimeOffsController@view')->name('pto.view');


Auth::routes();

Route::get('/home', 'HomeController@index');
