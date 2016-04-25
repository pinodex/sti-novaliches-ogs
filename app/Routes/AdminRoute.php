<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Controllers\AdminController;
use App\Services\Auth;

/**
 * Admin route
 * 
 * Admin route for /admin/ mount
 */
class AdminRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/', [AdminController::class, 'index'])
            ->bind('admin.index');

        $controller->get('/manage/faculty', [AdminController::class, 'manageFaculty'])
            ->bind('admin.manage.faculty');

        $controller->get('/manage/faculty/search', [AdminController::class, 'manageFaculty'])
            ->bind('admin.manage.faculty.search');

        $controller->match('/manage/faculty/add', [AdminController::class, 'addFaculty'])
            ->bind('admin.manage.faculty.add');

        $controller->match('/manage/faculty/{id}/delete', [AdminController::class, 'deleteFaculty'])
            ->bind('admin.manage.faculty.delete');

        $controller->match('/manage/faculty/{id}/edit', [AdminController::class, 'editFaculty'])
            ->bind('admin.manage.faculty.edit');

        $controller->before(function (Request $request, Application $app) {
            if (!$user = Auth::user()) {
                return $app->redirect($app->path('site.login', array(
                    'next' => urlencode($request->getRequestUri())
                )));
            }

            if (!in_array(get_class($this), $user->getProvider()->getAllowedRouteGroup())) {
                return $app->redirect($app->path('site.login'));
            }
        });
        
        return $controller;
    }
}
