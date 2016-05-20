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
use App\Controllers\Dashboard\DepartmentsController;

/**
 * Handles route for /dashboard/departments/ mount
 */
class DepartmentsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $factory = $app['controllers_factory'];
        $controller = new DepartmentsController();
        
        $factory->get('/', array($controller, 'index'))->bind('dashboard.departments');

        $factory->match('/add', array($controller, 'edit'))->bind('dashboard.departments.add')->value('id', null);

        $factory->match('/self', array($controller, 'self'))->bind('dashboard.departments.self');

        $factory->match('/{id}', array($controller, 'view'))->bind('dashboard.departments.view');

        $factory->match('/{id}/edit', array($controller, 'edit'))->bind('dashboard.departments.edit');

        $factory->match('/{id}/delete', array($controller, 'delete'))->bind('dashboard.departments.delete');
        
        return $factory;
    }
}
