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
use Symfony\Component\HttpFoundation\Request;
use App\Controllers\FacultyController;
use App\Services\Auth;

/**
 * Faculty route
 * 
 * Handles route for /faculty/grades mount
 */
class GradesRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/import',
            array('App\Controllers\Faculty\GradesController', 'index')
        )->bind('faculty.grades.import');

        $controller->match('/import/1',
            array('App\Controllers\Faculty\GradesController', 'import1')
        )->bind('faculty.grades.import.1');

        $controller->match('/import/2',
            array('App\Controllers\Faculty\GradesController', 'import2')
        )->bind('faculty.grades.import.2');

        $controller->match('/import/3',
            array('App\Controllers\Faculty\GradesController', 'import3')
        )->bind('faculty.grades.import.3');

        $controller->match('/import/4',
            array('App\Controllers\Faculty\GradesController', 'import4')
        )->bind('faculty.grades.import.4');

        return $controller;
    }
}
