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

        $controller->match('/summary',
            array('App\Controllers\Dashboard\FacultiesController', 'summary')
        )->bind('dashboard.faculties.summary');

        $controller->match('/add',
            array('App\Controllers\Dashboard\FacultiesController', 'edit')
        )->bind('dashboard.faculties.add')->value('id', null);

        $controller->match('/import',
            array('App\Controllers\Dashboard\FacultiesController', 'import')
        )->bind('dashboard.faculties.import');

        $controller->match('/import/1',
            array('App\Controllers\Dashboard\FacultiesController', 'import1')
        )->bind('dashboard.faculties.import.1');

        $controller->match('/import/2',
            array('App\Controllers\Dashboard\FacultiesController', 'import2')
        )->bind('dashboard.faculties.import.2');

        $controller->match('/import/3',
            array('App\Controllers\Dashboard\FacultiesController', 'import3')
        )->bind('dashboard.faculties.import.3');

        $controller->match('/import/4',
            array('App\Controllers\Dashboard\FacultiesController', 'import4')
        )->bind('dashboard.faculties.import.4');

        $controller->match('/{id}',
            array('App\Controllers\Dashboard\FacultiesController', 'view')
        )->bind('dashboard.faculties.view');

        $controller->match('/{id}/edit',
            array('App\Controllers\Dashboard\FacultiesController', 'edit')
        )->bind('dashboard.faculties.edit');

        $controller->match('/{id}/delete',
            array('App\Controllers\Dashboard\FacultiesController', 'delete')
        )->bind('dashboard.faculties.delete');

        return $controller;
    }
}
