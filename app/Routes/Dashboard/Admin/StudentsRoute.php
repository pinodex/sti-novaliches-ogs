<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Dashboard\Admin;

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
            array('App\Controllers\Dashboard\Admin\StudentsController', 'index')
        )->bind('dashboard.students');

        $controller->get('/search',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'index')
        )->bind('dashboard.students.search');

        $controller->match('/add',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'edit')
        )->bind('dashboard.students.add')->value('id', null);

        $controller->get('/{id}',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'view')
        )->bind('dashboard.students.view')->assert('id', '[\d+]{11}');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'edit')
        )->bind('dashboard.students.edit')->assert('id', '[\d+]{11}');

        $controller->match('/{id}/edit/grades',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'editGrades')
        )->bind('dashboard.students.edit.grades')->assert('id', '[\d+]{11}');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'delete')
        )->bind('dashboard.students.delete')->assert('id', '[\d+]{11}');
        
        $controller->match('/import',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'import')
        )->bind('dashboard.students.import');

        $controller->match('/import/1',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'import1')
        )->bind('dashboard.students.import.1');

        $controller->match('/import/2',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'import2')
        )->bind('dashboard.students.import.2');

        $controller->match('/import/3',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'import3')
        )->bind('dashboard.students.import.3');

        $controller->match('/import/4',
            array('App\Controllers\Dashboard\Admin\StudentsController', 'import4')
        )->bind('dashboard.students.import.4');
        
        return $controller;
    }
}
