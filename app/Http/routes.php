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

    Route::group(['prefix' => 'heads', 'as' => 'heads.'], function () {
        Route::get('/', 'HeadController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'HeadController@edit')->name('add');
        Route::match(['get', 'post'], '/{head}/edit', 'HeadController@edit')->name('edit');
        Route::match(['get', 'post'], '/{head}/delete', 'HeadController@delete')->name('delete');
    });

    Route::group(['prefix' => 'heads', 'as' => 'heads.'], function () {
        Route::get('/', 'HeadController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'HeadController@edit')->name('add');
        Route::match(['get', 'post'], '/{head}/edit', 'HeadController@edit')->name('edit');
        Route::match(['get', 'post'], '/{head}/delete', 'HeadController@delete')->name('delete');
    });

    Route::group(['prefix' => 'faculty', 'as' => 'faculty.'], function () {
        Route::get('/', 'FacultyController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'FacultyController@edit')->name('add');
        Route::match(['get', 'post'], '/{faculty}/edit', 'FacultyController@edit')->name('edit');
        Route::match(['get', 'post'], '/{faculty}/delete', 'FacultyController@delete')->name('delete');

        Route::get('/summary', 'FacultyController@summary')->name('summary');
        Route::get('/{faculty}', 'FacultyController@view')->name('view');
    });

    Route::group(['prefix' => 'guidance', 'as' => 'guidance.'], function () {
        Route::get('/', 'GuidanceController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'GuidanceController@edit')->name('add');
        Route::match(['get', 'post'], '/{guidance}/edit', 'GuidanceController@edit')->name('edit');
        Route::match(['get', 'post'], '/{guidance}/delete', 'GuidanceController@delete')->name('delete');
    });

    Route::group(['prefix' => 'departments', 'as' => 'departments.'], function () {
        Route::get('/', 'DepartmentController@index')->name('index');
        Route::get('/self', 'DepartmentController@self')->name('self');

        Route::match(['get', 'post'], '/add', 'DepartmentController@edit')->name('add');
        Route::match(['get', 'post'], '/{department}/edit', 'DepartmentController@edit')->name('edit');
        Route::match(['get', 'post'], '/{department}/delete', 'DepartmentController@delete')->name('delete');

        Route::get('/{department}', 'DepartmentController@view')->name('view');
    });

    Route::group(['prefix' => 'sections', 'as' => 'sections.'], function () {
        Route::get('/', 'SectionController@index')->name('index');
    });

    Route::group(['prefix' => 'students', 'as' => 'students.'], function () {
        Route::get('/', 'StudentController@index')->name('index');

        Route::match(['get', 'post'], '/add', 'StudentController@edit')->name('add');
        Route::match(['get', 'post'], '/{student}/edit', 'StudentController@edit')->name('edit');
        Route::match(['get', 'post'], '/{student}/delete', 'StudentController@delete')->name('delete');
        Route::match(['get', 'post'], '/{student}/grades/edit', 'StudentController@gradesEdit')->name('grades.edit');

        Route::get('/{student}', 'StudentController@view')->name('view');
    });

    Route::group(['namespace' => 'Import', 'prefix' => 'import', 'as' => 'import.'], function () {

        Route::get('/faculty', 'FacultyImportController@index')->name('faculty');
        Route::get('/students', 'StudentImportController@index')->name('students');
        
        Route::group(['prefix' => 'faculty', 'as' => 'faculty.'], function () {
            Route::match(['get', 'post'], '/upload', 'FacultyImportController@stepOne')->name('stepOne');
            Route::match(['get', 'post'], '/select', 'FacultyImportController@stepTwo')->name('stepTwo');
            Route::match(['get', 'post'], '/confirm', 'FacultyImportController@stepThree')->name('stepThree');

            Route::get('/finish', 'FacultyImportController@stepFour')->name('stepFour');
        });

        Route::group(['prefix' => 'students', 'as' => 'students.'], function () {
            Route::match(['get', 'post'], '/upload', 'StudentImportController@stepOne')->name('stepOne');
            Route::match(['get', 'post'], '/confirm', 'StudentImportController@stepTwo')->name('stepTwo');

            Route::get('/finish', 'StudentImportController@stepThree')->name('stepThree');
        });

    });

    Route::group(['prefix' => 'memo', 'as' => 'memo.'], function () {
        Route::get('/', 'MemoController@index')->name('index');
        Route::get('/{memo}/view', 'MemoController@view')->name('view');

        Route::match(['get', 'post'], '/send', 'MemoController@edit')->name('send');
    });

});
