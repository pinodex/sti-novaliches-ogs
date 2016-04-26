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
use Symfony\Component\HttpFoundation\Request;
use App\Services\Auth;

/**
 * Manage faculty admin route
 * 
 * Route group for /admin/ mount
 */
class ManageAdminRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/',
            array('App\Controllers\Admin\ManageAdminController', 'manageAdmin')
        )->bind('admin.manage.admin');

        $controller->get('/search',
            array('App\Controllers\Admin\ManageAdminController', 'manageAdmin')
        )->bind('admin.manage.admin.search');

        $controller->match('/add',
            array('App\Controllers\Admin\ManageAdminController', 'editAdmin')
        )->bind('admin.manage.admin.add')->value('id', null);

        $controller->match('/{id}/edit',
            array('App\Controllers\Admin\ManageAdminController', 'editAdmin')
        )->bind('admin.manage.admin.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Admin\ManageAdminController', 'deleteAdmin')
        )->bind('admin.manage.admin.delete');
        
        return $controller;
    }
}
