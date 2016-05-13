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
use App\Controllers\Dashboard\GradesImportController;

/**
 * Main route
 * 
 * Handles route for /dashboard/grades/import/ mount
 */
class GradesImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new GradesImportController();

        $factory->get('/', array($controller, 'index'))->bind('dashboard.grades.import');

        $factory->match('/1', array($controller, 'stepOne'))->bind('dashboard.grades.import.1');

        $factory->match('/2', array($controller, 'stepTwo'))->bind('dashboard.grades.import.2');

        $factory->match('/3', array($controller, 'stepThree'))->bind('dashboard.grades.import.3');

        $factory->match('/4', array($controller, 'stepFour'))->bind('dashboard.grades.import.4');
        
        return $factory;
    }
}
