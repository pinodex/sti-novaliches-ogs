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
 * Handles route for /faculty/student mount
 */
class StudentRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/{id}',
            array('App\Controllers\Faculty\StudentController', 'view')
        )->bind('faculty.students.view');

        $controller->match('/{id}/edit',
            array('App\Controllers\Faculty\StudentController', 'edit')
        )->bind('faculty.students.edit');

        return $controller;
    }
}
