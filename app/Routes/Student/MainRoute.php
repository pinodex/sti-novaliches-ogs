<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Student;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Student route
 * 
 * Handles route for /student/ mount
 */
class MainRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Student\MainController', 'index')
        )->bind('student.index');

        $controller->match('/top/{period}/{subject}',
            array('App\Controllers\Student\MainController', 'top')
        )->bind('student.top')->value('period', null)->value('subject', null);
        
        return $controller;
    }
}
