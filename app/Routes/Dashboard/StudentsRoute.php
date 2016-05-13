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
use App\Controllers\Dashboard\StudentsController;

/**
 * Main route
 * 
 * Handles route for /dashboard/students/ mount
 */
class StudentsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new StudentsController();

        $factory->get('/', array($controller, 'index'))->bind('dashboard.students');

        $factory->get('/search', array($controller, 'index'))->bind('dashboard.students.search');

        $factory->match('/add', array($controller, 'edit'))->bind('dashboard.students.add')->value('id', null);

        $factory->get('/{id}', array($controller, 'view'))->bind('dashboard.students.view')->assert('id', '[\d+]{11}');

        $factory->match('/{id}/edit', array($controller, 'edit'))->bind('dashboard.students.edit')->assert('id', '[\d+]{11}');

        $factory->match('/{id}/edit/grades', array($controller, 'editGrades'))->bind('dashboard.students.edit.grades')->assert('id', '[\d+]{11}');

        $factory->match('/{id}/delete', array($controller, 'delete'))->bind('dashboard.students.delete')->assert('id', '[\d+]{11}');
        
        return $factory;
    }
}
