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
class StudentsImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/',
            array('App\Controllers\Dashboard\StudentsImportController', 'index')
        )->bind('dashboard.students.import');

        $controller->match('/1',
            array('App\Controllers\Dashboard\StudentsImportController', 'firstStep')
        )->bind('dashboard.students.import.1');

        $controller->match('/2',
            array('App\Controllers\Dashboard\StudentsImportController', 'secondStep')
        )->bind('dashboard.students.import.2');

        $controller->match('/3',
            array('App\Controllers\Dashboard\StudentsImportController', 'thirdStep')
        )->bind('dashboard.students.import.3');
        
        return $controller;
    }
}
