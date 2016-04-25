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
use App\Controllers\StudentController;
use App\Services\Auth;

/**
 * Student route
 * 
 * Handles route for /student/ mount
 */
class StudentRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/', [StudentController::class, 'index'])->bind('student.index');

        $controller->match('/top/{period}/{subject}', [StudentController::class, 'top'])->bind('student.top')
            ->value('period', null)
            ->value('subject', null);

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
