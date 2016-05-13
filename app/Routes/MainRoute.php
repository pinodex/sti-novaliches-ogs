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
use App\Controllers\MainController;

/**
 * Main route
 * 
 * Handles route for / mount
 */
class MainRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new MainController();
        
        $factory->get('/', array($controller, 'index'))->bind('site.index');
        
        $factory->match('/login', array($controller, 'login'))->bind('site.login');

        $factory->get('/logout', array($controller, 'logout'))->bind('site.logout');
        
        return $factory;
    }
}
