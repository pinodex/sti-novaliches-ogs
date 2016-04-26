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
use App\Controllers\AdminController;
use App\Services\Auth;

/**
 * Manage faculty admin route
 * 
 * Route group for /admin/ mount
 */
class ManageStudentRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/import',
            array('App\Controllers\Admin\ManageStudentController', 'studentImport')
        )->bind('admin.manage.student.import');

        $controller->match('/import/1',
            array('App\Controllers\Admin\ManageStudentController', 'studentImport1')
        )->bind('admin.manage.student.import.1');

        $controller->match('/import/2',
            array('App\Controllers\Admin\ManageStudentController', 'studentImport2')
        )->bind('admin.manage.student.import.2');

        $controller->match('/import/3',
            array('App\Controllers\Admin\ManageStudentController', 'studentImport3')
        )->bind('admin.manage.student.import.3');

        $controller->match('/import/4',
            array('App\Controllers\Admin\ManageStudentController', 'studentImport4')
        )->bind('admin.manage.student.import.4');

        $controller->match('/add',
            array('App\Controllers\Admin\ManageStudentController', 'editStudent')
        )->bind('admin.manage.student.add')->value('id', null);

        $controller->match('/{id}/edit',
            array('App\Controllers\Admin\ManageStudentController', 'editStudent')
        )->bind('admin.manage.student.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Admin\ManageStudentController', 'deleteStudent')
        )->bind('admin.manage.student.delete');
        
        return $controller;
    }
}
