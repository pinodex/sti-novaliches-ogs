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
 * Handles route for /dashboard/faculties/ mount
 */
class FacultiesRoute implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $controller->get('/',
            array('App\Controllers\Dashboard\FacultiesController', 'index')
        )->bind('dashboard.faculties');

        $controller->get('/search',
            array('App\Controllers\Dashboard\FacultiesController', 'index')
        )->bind('dashboard.faculties.search');

        $controller->match('/add',
            array('App\Controllers\Dashboard\FacultiesController', 'edit')
        )->bind('dashboard.faculties.add')->value('id', null);

        $controller->match('/{id}',
            array('App\Controllers\Dashboard\FacultiesController', 'view')
        )->bind('dashboard.faculties.view');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\FacultiesController', 'edit')
        )->bind('dashboard.faculties.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\FacultiesController', 'delete')
        )->bind('dashboard.faculties.delete');
        
        $controller->match('/{id}/sections',
            array('App\Controllers\Dashboard\FacultiesController', 'sections')
        )->bind('dashboard.faculties.sections');

        return $controller;
    }
}
