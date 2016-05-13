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
use App\Controllers\Dashboard\StudentsImportController;

/**
 * Main route
 * 
 * Handles route for /dashboard/students/ mount
 */
class StudentsImportRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new StudentsImportController();

        $factory->match('/', array($controller, 'index'))->bind('dashboard.students.import');

        $factory->match('/1', array($controller, 'stepOne'))->bind('dashboard.students.import.1');

        $factory->match('/2', array($controller, 'stepTwo'))->bind('dashboard.students.import.2');

        $factory->match('/3', array($controller, 'stepThree'))->bind('dashboard.students.import.3');
        
        return $factory;
    }
}
