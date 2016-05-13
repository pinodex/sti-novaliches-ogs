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
use App\Controllers\Dashboard\FacultyController;

/**
 * Handles route for /dashboard/faculty/ mount
 */
class FacultyRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new FacultyController();
        
        $factory->get('/', array($controller, 'index'))->bind('dashboard.faculty');

        $factory->get('/search', array($controller, 'index'))->bind('dashboard.faculty.search');

        $factory->match('/summary', array($controller, 'summary'))->bind('dashboard.faculty.summary');

        $factory->match('/add', array($controller, 'edit'))->bind('dashboard.faculty.add')->value('id', null);

        $factory->match('/{id}', array($controller, 'view'))->bind('dashboard.faculty.view')->assert('id', '\d+');

        $factory->match('/{id}/edit', array($controller, 'edit'))->bind('dashboard.faculty.edit')->assert('id', '\d+');

        $factory->match('/{id}/delete', array($controller, 'delete'))->bind('dashboard.faculty.delete')->assert('id', '\d+');

        return $factory;
    }
}
