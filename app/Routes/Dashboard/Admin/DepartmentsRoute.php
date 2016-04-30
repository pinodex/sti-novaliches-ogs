<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Dashboard\Admin;

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
            array('App\Controllers\Dashboard\Admin\DepartmentsController', 'index')
        )->bind('dashboard.departments');

        $controller->match('/add',
            array('App\Controllers\Dashboard\Admin\DepartmentsController', 'edit')
        )->bind('dashboard.departments.add')->value('id', null);

        $controller->match('/{id}',
            array('App\Controllers\Dashboard\Admin\DepartmentsController', 'view')
        )->bind('dashboard.departments.view');

        $controller->match('/{id}/search',
            array('App\Controllers\Dashboard\Admin\DepartmentsController', 'view')
        )->bind('dashboard.departments.view.search');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\Admin\DepartmentsController', 'edit')
        )->bind('dashboard.departments.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\Admin\DepartmentsController', 'delete')
        )->bind('dashboard.departments.delete');
        
        return $controller;
    }
}
