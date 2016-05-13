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
use App\Controllers\Dashboard\AdminsController;

/**
 * Handles route for /dashboard/administrators/ mount
 */
class AdminsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new AdminsController();
        
        $factory->get('/', array($controller, 'index'))->bind('dashboard.admins');

        $factory->get('/search', array($controller, 'index'))->bind('dashboard.admins.search');

        $factory->match('/add', array($controller, 'edit'))->bind('dashboard.admins.add')->value('id', null);

        $factory->match('/{id}/edit', array($controller, 'edit'))->bind('dashboard.admins.edit');

        $factory->match('/{id}/delete', array($controller, 'delete'))->bind('dashboard.admins.delete');
        
        return $factory;
    }
}
