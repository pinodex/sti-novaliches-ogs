<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Dashboard;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Handles route for /dashboard/faculty/ mount
 */
class FacultyRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Dashboard\FacultyController', 'index')
        )->bind('dashboard.faculty');

        $controller->get('/search',
            array('App\Controllers\Dashboard\FacultyController', 'index')
        )->bind('dashboard.faculty.search');

        $controller->match('/summary',
            array('App\Controllers\Dashboard\FacultyController', 'summary')
        )->bind('dashboard.faculty.summary');

        $controller->match('/add',
            array('App\Controllers\Dashboard\FacultyController', 'edit')
        )->bind('dashboard.faculty.add')->value('id', null);

        $controller->match('/{id}',
            array('App\Controllers\Dashboard\FacultyController', 'view')
        )->bind('dashboard.faculty.view')->assert('id', '\d+');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\FacultyController', 'edit')
        )->bind('dashboard.faculty.edit')->assert('id', '\d+');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\FacultyController', 'delete')
        )->bind('dashboard.faculty.delete')->assert('id', '\d+');

        return $controller;
    }
}
