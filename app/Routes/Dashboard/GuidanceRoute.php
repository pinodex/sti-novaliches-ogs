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
use App\Controllers\Dashboard\GuidanceController;

/**
 * Handles route for /dashboard/guidance/ mount
 */
class GuidanceRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new GuidanceController();

        $factory->get('/', array($controller, 'index'))->bind('dashboard.guidance');

        $factory->get('/search', array($controller, 'index'))->bind('dashboard.guidance.search');

        $factory->match('/add', array($controller, 'edit'))->bind('dashboard.guidance.add')->value('id', null);

        $factory->match('/{id}/edit', array($controller, 'edit'))->bind('dashboard.guidance.edit');

        $factory->match('/{id}/delete', array($controller, 'delete'))->bind('dashboard.guidance.delete');

        return $factory;
    }
}
