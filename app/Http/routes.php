<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Route::get('/', ['as' => 'index', 'uses'  => 'MainController@index']);

Route::match(['get', 'post'], '/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);

Route::match(['get', 'post'], '/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout']);

Route::group(['namespace' => 'Help', 'prefix' => 'help', 'as' => 'help.'], function () {

    Route::get('/', 'MainController@index')->name('index');
    Route::get('/about', 'MainController@about')->name('about');

});

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

    Route::group(['prefix' => 'grades', 'as' => 'grades.'], function () {
        Route::get('/', 'GradeController@index')->name('index');
        Route::get('/compare', 'GradeController@compare')->name('compare');

        Route::group(['prefix' => 'compare', 'as' => 'compare.'], function () {
            Route::match(['get', 'post'], '/upload', 'GradeCompareController@upload')->name('upload');

            Route::get('/diff', 'GradeCompareController@diff')->name('diff');
        });
    });

    Route::group(['prefix' => 'memo', 'as' => 'memo.'], function () {
        Route::get('/', 'MemoController@index')->name('index');
        Route::get('/{memo}/view', 'MemoController@view')->name('view');

        Route::match(['get', 'post'], '/send', 'MemoController@send')->name('send');
    });

    Route::group(['namespace' => 'Import', 'prefix' => 'import', 'as' => 'import.'], function () {

        Route::get('/faculty', 'FacultyImportController@index')->name('faculty');
        Route::get('/students', 'StudentImportController@index')->name('students');
        Route::get('/grades', 'GradeImportController@index')->name('grades');
        
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

        Route::group(['prefix' => 'grades', 'as' => 'grades.'], function () {
            Route::match(['get', 'post'], '/upload', 'GradeImportController@stepOne')->name('stepOne');
            Route::match(['get', 'post'], '/select', 'GradeImportController@stepTwo')->name('stepTwo');
            Route::match(['get', 'post'], '/confirm', 'GradeImportController@stepThree')->name('stepThree');

            Route::get('/finish', 'GradeImportController@stepFour')->name('stepFour');
        });

    });

    Route::group(['namespace' => 'Settings', 'prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::match(['get', 'post'], '/', 'MainController@index')->name('index');

        Route::get('/maintenance', 'MainController@maintenance')->name('maintenance');

        Route::match(['get', 'post'], '/maintenance/purge', 'MainController@maintenancePurge')->name('maintenancePurge');

        Route::group(['prefix' => 'google-auth', 'as' => 'googleauth.'], function () {
            Route::get('/', 'GoogleAuthController@index')->name('index');
            Route::get('/connect', 'GoogleAuthController@connect')->name('connect');
            Route::get('/disconnect', 'GoogleAuthController@disconnect')->name('disconnect');

            Route::match(['get', 'post'], '/client-secret', 'GoogleAuthController@clientSecret')->name('clientSecret');
        });

        Route::group(['prefix' => 'email-delivery', 'as' => 'emaildelivery.'], function () {
            Route::match(['get', 'post'], '/', 'EmailDeliveryController@index')->name('index');
        });
    });

});

Route::group(['namespace' => 'Api', 'prefix' => 'api', 'as' => 'api.', 'middleware' => ['api']], function () {

    Route::group(['namespace' => 'V1', 'prefix' => 'v1', 'as' => '1.'], function () {
        Route::resource('student', 'StudentController', ['only' => ['show']]);
        Route::resource('grades', 'GradeController', ['only' => ['show']]);
    });

});
