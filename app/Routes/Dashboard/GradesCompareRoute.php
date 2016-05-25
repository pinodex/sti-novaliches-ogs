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
use App\Controllers\Dashboard\GradesCompareController;

/**
 * Main route
 * 
 * Handles route for /dashboard/grades/ mount
 */
class GradesCompareRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new GradesCompareController();

        $factory->get('/', array($controller, 'index'))->bind('dashboard.grades.compare');

        $factory->match('/upload', array($controller, 'upload'))->bind('dashboard.grades.compare.upload');

        $factory->match('/diff', array($controller, 'diff'))->bind('dashboard.grades.compare.diff');
        
        return $factory;
    }
}
