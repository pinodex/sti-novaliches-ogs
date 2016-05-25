<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app['auth.providers'] = array(
    'App\Providers\StudentProvider',
    'App\Providers\GuidanceProvider',
    'App\Providers\FacultyProvider',
    'App\Providers\HeadProvider',
    'App\Providers\AdminProvider'
);

$app['auth.protected_controllers'] = array(
    'App\Controllers\Dashboard\AdminsController',
    'App\Controllers\Dashboard\DepartmentsController',
    'App\Controllers\Dashboard\FacultyController',
    'App\Controllers\Dashboard\FacultyImportController',
    'App\Controllers\Dashboard\GradesCompareController',
    'App\Controllers\Dashboard\GradesController',
    'App\Controllers\Dashboard\GradesImportController',
    'App\Controllers\Dashboard\GuidanceController',
    'App\Controllers\Dashboard\HeadsController',
    'App\Controllers\Dashboard\MainController',
    'App\Controllers\Dashboard\MemosController',
    'App\Controllers\Dashboard\SectionsController',
    'App\Controllers\Dashboard\SettingsController',
    'App\Controllers\Dashboard\StudentsController',
    'App\Controllers\Dashboard\StudentsImportController',
    'App\Controllers\Student\MainController'
);
