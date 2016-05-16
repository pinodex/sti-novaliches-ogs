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
use App\Controllers\Dashboard\MemosController;

/**
 * Handles route for /dashboard/memos/ mount
 */
class MemosRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new MemosController();
        
        $factory->get('/', array($controller, 'index'))->bind('dashboard.memos');

        $factory->get('/search', array($controller, 'index'))->bind('dashboard.memos.search');

        $factory->match('/add', array($controller, 'add'))->bind('dashboard.memos.add');

        $factory->get('/{id}', array($controller, 'view'))->bind('dashboard.memos.view');

        return $factory;
    }
}
