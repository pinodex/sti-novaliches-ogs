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
use App\Controllers\Dashboard\GradesController;

/**
 * Main route
 * 
 * Handles route for /dashboard/grades/ mount
 */
class GradesRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new GradesController();

        $factory->get('/', array($controller, 'index'))->bind('dashboard.grades');
        
        return $factory;
    }
}
