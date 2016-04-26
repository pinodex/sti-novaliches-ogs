<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Routes\Admin;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Manage faculty admin route
 * 
 * Route group for /admin/ mount
 */
class ManageFacultyRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/',
            array('App\Controllers\Admin\ManageFacultyController', 'manageFaculty')
        )->bind('admin.manage.faculty');

        $controller->get('/search',
            array('App\Controllers\Admin\ManageFacultyController', 'manageFaculty')
        )->bind('admin.manage.faculty.search');

        $controller->match('/add',
            array('App\Controllers\Admin\ManageFacultyController', 'editFaculty')
        )->bind('admin.manage.faculty.add')->value('id', null);

        $controller->match('/{id}/edit',
            array('App\Controllers\Admin\ManageFacultyController', 'editFaculty')
        )->bind('admin.manage.faculty.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Admin\ManageFacultyController', 'deleteFaculty')
        )->bind('admin.manage.faculty.delete');

        return $controller;
    }
}
