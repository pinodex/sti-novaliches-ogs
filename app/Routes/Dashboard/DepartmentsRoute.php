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

/**
 * Handles route for /dashboard/departments/ mount
 */
class DepartmentsRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Dashboard\DepartmentsController', 'index')
        )->bind('dashboard.departments');

        $controller->match('/add',
            array('App\Controllers\Dashboard\DepartmentsController', 'edit')
        )->bind('dashboard.departments.add')->value('id', null);

        $controller->match('/self',
            array('App\Controllers\Dashboard\DepartmentsController', 'self')
        )->bind('dashboard.departments.self');

        $controller->match('/global-deadline',
            array('App\Controllers\Dashboard\DepartmentsController', 'globalDeadline')
        )->bind('dashboard.departments.globalDeadline');

        $controller->match('/{id}',
            array('App\Controllers\Dashboard\DepartmentsController', 'view')
        )->bind('dashboard.departments.view');

        $controller->match('/{id}/search',
            array('App\Controllers\Dashboard\DepartmentsController', 'view')
        )->bind('dashboard.departments.view.search');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\DepartmentsController', 'edit')
        )->bind('dashboard.departments.edit');

        $controller->match('/{id}/settings',
            array('App\Controllers\Dashboard\DepartmentsController', 'settings')
        )->bind('dashboard.departments.settings');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\DepartmentsController', 'delete')
        )->bind('dashboard.departments.delete');
        
        return $controller;
    }
}
