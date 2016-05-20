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
use App\Controllers\Dashboard\HeadsController;

/**
 * Handles route for /dashboard/heads/ mount
 */
class HeadsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new HeadsController();
        
        $factory->get('/', array($controller, 'index'))->bind('dashboard.heads');

        $factory->match('/add', array($controller, 'edit'))->bind('dashboard.heads.add')->value('id', null);

        $factory->match('/{id}/edit', array($controller, 'edit'))->bind('dashboard.heads.edit');

        $factory->match('/{id}/delete', array($controller, 'delete'))->bind('dashboard.heads.delete');
        
        return $factory;
    }
}
