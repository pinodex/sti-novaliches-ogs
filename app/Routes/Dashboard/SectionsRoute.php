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
use App\Controllers\Dashboard\SectionsController;

/**
 * Handles route for /dashboard/sections/ mount
 */
class SectionsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new SectionsController();
        
        $factory->get('/', array($controller, 'index'))->bind('dashboard.sections');

        $factory->get('/{section}', array($controller, 'view'))->bind('dashboard.sections.view');

        return $factory;
    }
}
