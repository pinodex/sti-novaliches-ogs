<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', [
    'as'    => 'index',
    'uses'  => 'MainController@index'
]);

Route::match(['get', 'post'], '/login', [
    'as' => 'login',
    'uses' => 'MainController@login'
]);

Route::match(['get', 'post'], '/logout', [
    'as' => 'logout',
    'uses' => 'MainController@logout'
]);

Route::get('/credits', [
    'as'    => 'credits',
    'uses'  => 'MainController@credits'
]);

Route::group(['namespace' => 'Student', 'prefix' => 'student', 'as' => 'student.'], function() {

    Route::get('/', 'MainController@index')->name('index');
    
    Route::match(['get', 'post'], '/top/{period?}/{subject?}', 'MainController@top')->name('top');
    Route::match(['get', 'post'], '/account', 'MainController@account')->name('account');

});

Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard', 'as' => 'dashboard.'], function() {

    /*
    Route::controller('/', 'MainController', [
        'getIndex'  => 'index',
        'getTop'    => 'top'
    ]);
    */

});
