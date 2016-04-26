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
use App\Routes\Faculty;
use App\Routes\Admin;
use App\Services\Auth;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app->mount('/', new Routes\MainRoute);

$app->mount('/student', new Student\MainRoute);

$app->mount('/faculty', new Faculty\MainRoute);
$app->mount('/faculty/grades', new Faculty\GradesRoute);
$app->mount('/faculty/student', new Faculty\StudentRoute);

$app->mount('/admin', new Admin\MainRoute);
$app->mount('/admin/manage/admin', new Admin\ManageAdminRoute);
$app->mount('/admin/manage/faculty', new Admin\ManageFacultyRoute);
$app->mount('/admin/manage/student', new Admin\ManageStudentRoute);

$protectedControllers = array(
    'App\Controllers\Admin\MainController',
    'App\Controllers\Admin\ManageAdminController',
    'App\Controllers\Admin\ManageFacultyController',
    'App\Controllers\Admin\ManageStudentController',
    'App\Controllers\Faculty\MainController',
    'App\Controllers\Faculty\GradesController',
    'App\Controllers\Faculty\StudentController',
    'App\Controllers\Student\MainController'
);

$app->before(function(Request $request, Application $app) use ($protectedControllers) {
    $currentController = '';
    
    if (is_array($request->get('_controller'))) {
        $currentController = $request->get('_controller')[0];
    }
    
    if ($user = Auth::user()) {
        $provider = $user->getProvider();

        if (in_array($currentController, $protectedControllers) &&
            !in_array($currentController, $provider->getAllowedControllers())) {
            
            return $app->redirect($app->path($provider->getRedirectRoute()));
        }

        return;
    };

    if (in_array($currentController, $protectedControllers)) {
        return $app->redirect($app->path('site.login', array(
            'next' => urlencode($request->getRequestUri())
        )));
    }
});
