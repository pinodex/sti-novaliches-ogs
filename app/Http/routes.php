<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

Route::group(['namespace' => 'Student', 'prefix' => 'student', 'as' => 'student.'], function () {

    Route::get('/', 'MainController@index')->name('index');
    
    Route::match(['get', 'post'], '/top/{period?}/{subject?}', 'MainController@top')->name('top');
    Route::match(['get', 'post'], '/account', 'MainController@account')->name('account');

});

Route::group(['namespace' => 'Dashboard', 'prefix' => 'dashboard', 'as' => 'dashboard.'], function () {

    Route::get('/', 'MainController@index')->name('index');
    Route::match(['get', 'post'], '/account', 'MainController@account')->name('account');

    Route::group(['prefix' => 'admins', 'as' => 'admins.'], function () {

        Route::get('/', 'AdminController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'AdminController@edit')->name('add');

        Route::match(['get', 'post'], '/{admin}/edit', 'AdminController@edit')->name('edit');

        Route::match(['get', 'post'], '/{admin}/delete', 'AdminController@delete')->name('delete');

    });

});
