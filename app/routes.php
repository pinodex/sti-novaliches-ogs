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
use App\Services\View;
use App\Services\Auth;
use App\Services\FlashBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Capsule\Manager as DB;


$app->mount('/', new Routes\MainRoute);

$app->mount('/student',                     new Student\MainRoute);

$app->mount('/dashboard',                   new Dashboard\MainRoute);
$app->mount('/dashboard/admins',            new Dashboard\AdminsRoute);
$app->mount('/dashboard/heads',             new Dashboard\HeadsRoute);
$app->mount('/dashboard/faculty',           new Dashboard\FacultyRoute);
$app->mount('/dashboard/guidance',          new Dashboard\GuidanceRoute);
$app->mount('/dashboard/departments',       new Dashboard\DepartmentsRoute);
$app->mount('/dashboard/students',          new Dashboard\StudentsRoute);
$app->mount('/dashboard/grades',            new Dashboard\GradesRoute);
$app->mount('/dashboard/settings',          new Dashboard\SettingsRoute);

$app->mount('/dashboard/faculty/import',    new Dashboard\FacultyImportRoute);
$app->mount('/dashboard/students/import',   new Dashboard\StudentsImportRoute);
$app->mount('/dashboard/grades/import',     new Dashboard\GradesImportRoute);

$app->before(function (Request $request, Application $app) {
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

$app->after(function (Request $request, Response $response) {
    $response->headers->add(array(
        'X-Frame-Options'   => 'SAMEORIGIN'
    ));
});

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    if ($code == 404) {
        return View::render('_error/404');
    }
});
