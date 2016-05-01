<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Silex\Application;
use App\Routes;
use App\Routes\Student;
use App\Routes\Dashboard;
use App\Routes\Faculty;
use App\Routes\Admin;
use App\Services\Auth;
use App\Services\Session\FlashBag;
use Symfony\Component\HttpFoundation\Request;

$app->mount('/', new Routes\MainRoute);

$app->mount('/student',                 new Student\MainRoute);

$app->mount('/dashboard',               new Dashboard\MainRoute);
$app->mount('/dashboard/admins',        new Dashboard\AdminsRoute);
$app->mount('/dashboard/heads',         new Dashboard\HeadsRoute);
$app->mount('/dashboard/faculties',     new Dashboard\FacultiesRoute);
$app->mount('/dashboard/departments',   new Dashboard\DepartmentsRoute);
$app->mount('/dashboard/students',      new Dashboard\StudentsRoute);
$app->mount('/dashboard/sections',      new Dashboard\SectionsRoute);
$app->mount('/dashboard/grades',      new Dashboard\GradesRoute);
$app->mount('/dashboard/settings',      new Dashboard\SettingsRoute);

$app->before(function(Request $request, Application $app) {
    if (is_array($request->get('_controller')) &&
        !Auth::isAllowed($request->get('_controller'))) {
        
        if (Auth::user()) {
            FlashBag::add('messages', 'danger>>>You are not allowed to perform this action');
        }

        return $app->redirect($app->path('site.login', array(
            'next' => urlencode($request->getRequestUri())
        )));
    }
});
