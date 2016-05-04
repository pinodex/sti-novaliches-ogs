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
class FacultyImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/',
            array('App\Controllers\Dashboard\FacultyImportController', 'index')
        )->bind('dashboard.faculty.import');

        $controller->match('/1',
            array('App\Controllers\Dashboard\FacultyImportController', 'stepOne')
        )->bind('dashboard.faculty.import.1');

        $controller->match('/2',
            array('App\Controllers\Dashboard\FacultyImportController', 'stepTwo')
        )->bind('dashboard.faculty.import.2');

        $controller->match('/3',
            array('App\Controllers\Dashboard\FacultyImportController', 'stepThree')
        )->bind('dashboard.faculty.import.3');

        $controller->match('/4',
            array('App\Controllers\Dashboard\FacultyImportController', 'stepFour')
        )->bind('dashboard.faculty.import.4');

        return $controller;
    }
}
