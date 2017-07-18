<?php

/*
 * This file is part of the TAS System for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

Route::get('/', ['as' => 'index', 'uses' => 'MainController@index']);

Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {

    Route::match(['get', 'post'], '/login', 'AuthController@login')->name('login')->middleware('guest');
    Route::get('/logout', 'AuthController@logout')->name('logout');

    Route::get('/sso_callback', 'AuthController@ssoCallback')->middleware('guest');
    
});

Route::group([
    'namespace'     => 'Dashboard',
    'prefix'        => 'dashboard',
    'as'            => 'dashboard.',
    'middleware'    => [
        'auth', 'require_password_change'
    ]], function () {

    Route::get('/', 'MainController@index')->name('index');

});
