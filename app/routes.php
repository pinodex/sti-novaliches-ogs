<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Routes;
use App\Routes\Student;
use App\Routes\Dashboard;
use App\Routes\Faculty;
use App\Routes\Admin;
use App\Services\Auth;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app->mount('/', new Routes\MainRoute);

$app->mount('/student',                 new Student\MainRoute);

$app->mount('/dashboard',               new Dashboard\MainRoute);
$app->mount('/dashboard/admins',        new Dashboard\Admin\AdminsRoute);
$app->mount('/dashboard/heads',         new Dashboard\Admin\HeadsRoute);
$app->mount('/dashboard/faculties',     new Dashboard\Admin\FacultiesRoute);
$app->mount('/dashboard/departments',   new Dashboard\Admin\DepartmentsRoute);

$app->mount('/faculty', new Faculty\MainRoute);
$app->mount('/faculty/grades', new Faculty\GradesRoute);
$app->mount('/faculty/student', new Faculty\StudentRoute);

$app->mount('/admin', new Admin\MainRoute);
$app->mount('/admin/manage/admin', new Admin\ManageAdminRoute);
$app->mount('/admin/manage/faculty', new Admin\ManageFacultyRoute);
$app->mount('/admin/manage/student', new Admin\ManageStudentRoute);

$app->before(function(Request $request, Application $app) {
    $currentController = '';
    
    if (is_array($request->get('_controller'))) {
        $currentController = $request->get('_controller')[0];
    }
    
    if (!Auth::isAllowed($currentController)) {
        return $app->redirect($app->path('site.login', array(
            'next' => urlencode($request->getRequestUri())
        )));
    }
});
