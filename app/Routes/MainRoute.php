<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Main route
 * 
 * Handles route for / mount
 */
class MainRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/', [MainController::class, 'index'])->bind('site.index');

        $controller->match('/login', [MainController::class, 'login'])->bind('site.login');

        $controller->get('/logout', [MainController::class, 'logout'])->bind('site.logout');
        
        return $controller;
    }
}
