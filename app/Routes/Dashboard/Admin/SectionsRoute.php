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
 * Main route
 * 
 * Handles route for /dashboard/sections/ mount
 */
class SectionsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Dashboard\Admin\SectionsController', 'index')
        )->bind('dashboard.sections');

        $controller->match('/add',
            array('App\Controllers\Dashboard\Admin\SectionsController', 'edit')
        )->bind('dashboard.sections.add')->value('id', null);
        
        return $controller;
    }
}
