<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Faculty;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Faculty route
 * 
 * Handles route for /faculty/ mount
 */
class MainRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Faculty\MainController', 'index')
        )->bind('faculty.index');

        $controller->get('/search',
            array('App\Controllers\Faculty\MainController', 'index')
        )->bind('faculty.search');

        return $controller;
    }
}
