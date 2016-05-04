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
 * Handles route for /dashboard/guidance/ mount
 */
class GuidanceRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/',
            array('App\Controllers\Dashboard\GuidanceController', 'index')
        )->bind('dashboard.guidance');

        $controller->get('/search',
            array('App\Controllers\Dashboard\GuidanceController', 'index')
        )->bind('dashboard.guidance.search');

        $controller->match('/add',
            array('App\Controllers\Dashboard\GuidanceController', 'edit')
        )->bind('dashboard.guidance.add')->value('id', null);

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\GuidanceController', 'edit')
        )->bind('dashboard.guidance.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\GuidanceController', 'delete')
        )->bind('dashboard.guidance.delete');

        return $controller;
    }
}