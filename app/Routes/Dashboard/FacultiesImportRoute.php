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
 * Handles route for /dashboard/faculties/ mount
 */
class FacultiesImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/',
            array('App\Controllers\Dashboard\FacultiesImportController', 'index')
        )->bind('dashboard.faculties.import');

        $controller->match('/1',
            array('App\Controllers\Dashboard\FacultiesImportController', 'stepOne')
        )->bind('dashboard.faculties.import.1');

        $controller->match('/$data2',
            array('App\Controllers\Dashboard\FacultiesImportController', 'stepTwo')
        )->bind('dashboard.faculties.import.2');

        $controller->match('/$data3',
            array('App\Controllers\Dashboard\FacultiesImportController', 'stepThree')
        )->bind('dashboard.faculties.import.3');

        $controller->match('/$data4',
            array('App\Controllers\Dashboard\FacultiesImportController', 'stepFour')
        )->bind('dashboard.faculties.import.4');

        return $controller;
    }
}
