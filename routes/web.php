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
    'as' => 'admin::',
    'prefix' => 'admin',
    'namespace' => 'Admin',
], function() {
    Route::get('/', 'AdminPaidTimeOffsController@index')->name('dashboard');
    Route::get('/ptos/{id}/edit', 'AdminPaidTimeOffsController@edit')->name('pto.edit');
});

Route::get('/{year?}', 'PaidTimeOffsController@index')
    ->name('pto.index')
    ->where([
        'year' => '[0-9]{4}'
    ]);

Route::post('/ptos/store', 'PaidTimeOffsController@store')
    ->name('pto.store');

Route::get('/ptos/{id}/view', 'PaidTimeOffsController@view')->name('pto.view');


Auth::routes();

Route::get('/home', 'HomeController@index');
