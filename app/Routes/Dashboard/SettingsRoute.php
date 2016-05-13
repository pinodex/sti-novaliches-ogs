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
use App\Controllers\Dashboard\SettingsController;

/**
 * Handles route for /dashboard/settings/ mount
 */
class SettingsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new SettingsController();
        
        $factory->match('/', array($controller, 'index'))->bind('dashboard.settings');

        $factory->match('/maintenance', array($controller, 'maintenance'))->bind('dashboard.settings.maintenance');

        $factory->match('/maintenance/clear', array($controller, 'clear'))->bind('dashboard.settings.maintenance.clear');

        $factory->match('/maintenance/database-cleanup', array($controller, 'databaseCleanup'))->bind('dashboard.settings.maintenance.databaseCleanup');
        
        return $factory;
    }
}
