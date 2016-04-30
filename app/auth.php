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
    'App\Services\Auth\Provider\StudentProvider',
    'App\Services\Auth\Provider\FacultyProvider',
    'App\Services\Auth\Provider\HeadProvider',
    'App\Services\Auth\Provider\AdminProvider',
);

$app['auth.protected_controllers'] = array(
    'App\Controllers\Dashboard\MainController',
    'App\Controllers\Dashboard\Admin\AdminsController',
    'App\Controllers\Dashboard\Admin\HeadsController',
    'App\Controllers\Dashboard\Admin\FacultiesController',
    'App\Controllers\Dashboard\Admin\DepartmentsController',
    'App\Controllers\Student\MainController'
);
