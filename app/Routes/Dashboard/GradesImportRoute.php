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
 * Handles route for /dashboard/grades/import/ mount
 */
class GradesImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/',
            array('App\Controllers\Dashboard\GradesImportController', 'index')
        )->bind('dashboard.grades.import');

        $controller->match('/1',
            array('App\Controllers\Dashboard\GradesImportController', 'stepOne')
        )->bind('dashboard.grades.import.1');

        $controller->match('/2',
            array('App\Controllers\Dashboard\GradesImportController', 'stepTwo')
        )->bind('dashboard.grades.import.2');

        $controller->match('/3',
            array('App\Controllers\Dashboard\GradesImportController', 'stepThree')
        )->bind('dashboard.grades.import.3');

        $controller->match('/4',
            array('App\Controllers\Dashboard\GradesImportController', 'stepFour')
        )->bind('dashboard.grades.import.4');
        
        return $controller;
    }
}
