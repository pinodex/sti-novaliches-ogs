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
 * Handles route for /dashboard/administrators/ mount
 */
class AdminsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Dashboard\AdminsController', 'index')
        )->bind('dashboard.admins');

        $controller->get('/search',
            array('App\Controllers\Dashboard\AdminsController', 'index')
        )->bind('dashboard.admins.search');

        $controller->match('/add',
            array('App\Controllers\Dashboard\AdminsController', 'edit')
        )->bind('dashboard.admins.add')->value('id', null);

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\AdminsController', 'edit')
        )->bind('dashboard.admins.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\AdminsController', 'delete')
        )->bind('dashboard.admins.delete');
        
        return $controller;
    }
}
