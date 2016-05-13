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
use App\Controllers\Dashboard\FacultyImportController;

/**
 * Handles route for /dashboard/faculty/ mount
 */
class FacultyImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new FacultyImportController();

        $factory->match('/', array($controller, 'index'))->bind('dashboard.faculty.import');

        $factory->match('/1', array($controller, 'stepOne'))->bind('dashboard.faculty.import.1');

        $factory->match('/2', array($controller, 'stepTwo'))->bind('dashboard.faculty.import.2');

        $factory->match('/3', array($controller, 'stepThree'))->bind('dashboard.faculty.import.3');

        $factory->match('/4', array($controller, 'stepFour'))->bind('dashboard.faculty.import.4');

        return $factory;
    }
}
