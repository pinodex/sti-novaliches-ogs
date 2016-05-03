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
 * Main route
 * 
 * Handles route for /dashboard/students/ mount
 */
class StudentsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/',
            array('App\Controllers\Dashboard\StudentsController', 'index')
        )->bind('dashboard.students');

        $controller->get('/search',
            array('App\Controllers\Dashboard\StudentsController', 'index')
        )->bind('dashboard.students.search');

        $controller->match('/add',
            array('App\Controllers\Dashboard\StudentsController', 'edit')
        )->bind('dashboard.students.add')->value('id', null);

        $controller->get('/{id}',
            array('App\Controllers\Dashboard\StudentsController', 'view')
        )->bind('dashboard.students.view')->assert('id', '[\d+]{11}');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\StudentsController', 'edit')
        )->bind('dashboard.students.edit')->assert('id', '[\d+]{11}');

        $controller->match('/{id}/edit/grades',
            array('App\Controllers\Dashboard\StudentsController', 'editGrades')
        )->bind('dashboard.students.edit.grades')->assert('id', '[\d+]{11}');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\StudentsController', 'delete')
        )->bind('dashboard.students.delete')->assert('id', '[\d+]{11}');
        
        return $controller;
    }
}
