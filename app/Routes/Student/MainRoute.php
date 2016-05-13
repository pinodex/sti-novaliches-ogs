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
use App\Controllers\Student\MainController;

/**
 * Student route
 * 
 * Handles route for /student/ mount
 */
class MainRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new MainController();
        
        $factory->get('/', array($controller, 'index'))->bind('student.index');

        $factory->match('/account', array($controller, 'account'))->bind('student.account');

        $factory->match('/top/{period}/{subject}', array($controller, 'top'))->bind('student.top')
            ->value('period', null)->value('subject', null);
        
        return $factory;
    }
}
