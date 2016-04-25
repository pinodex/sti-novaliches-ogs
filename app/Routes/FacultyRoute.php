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
use App\Controllers\FacultyController;
use App\Services\Auth;

/**
 * Faculty route
 * 
 * Handles route for /faculty/ mount
 */
class FacultyRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/', [FacultyController::class, 'index'])
            ->bind('faculty.index');

        $controller->get('/search', [FacultyController::class, 'index'])
            ->bind('faculty.search');

        $controller->get('/students/{id}', [FacultyController::class, 'studentsView'])
            ->bind('faculty.students.view');

        $controller->match('/students/{id}/edit', [FacultyController::class, 'studentsEdit'])
            ->bind('faculty.students.edit');

        $controller->match('/grades/import', [FacultyController::class, 'gradesImport'])
            ->bind('faculty.grades.import');

        $controller->match('/grades/import/1', [FacultyController::class, 'gradesImport1'])
            ->bind('faculty.grades.import.1');

        $controller->match('/grades/import/2', [FacultyController::class, 'gradesImport2'])
            ->bind('faculty.grades.import.2');

        $controller->match('/grades/import/3', [FacultyController::class, 'gradesImport3'])
            ->bind('faculty.grades.import.3');

        $controller->match('/grades/import/4', [FacultyController::class, 'gradesImport4'])
            ->bind('faculty.grades.import.4');

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
