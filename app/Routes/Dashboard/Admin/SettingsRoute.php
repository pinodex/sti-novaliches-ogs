<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Dashboard\Admin;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Handles route for /dashboard/settings/ mount
 */
class SettingsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->match('/',
            array('App\Controllers\Dashboard\Admin\SettingsController', 'index')
        )->bind('dashboard.settings');

        $controller->match('/maintenance',
            array('App\Controllers\Dashboard\Admin\SettingsController', 'maintenance')
        )->bind('dashboard.settings.maintenance');

        $controller->match('/maintenance/clear',
            array('App\Controllers\Dashboard\Admin\SettingsController', 'clear')
        )->bind('dashboard.settings.maintenance.clear');
        
        return $controller;
    }
}
