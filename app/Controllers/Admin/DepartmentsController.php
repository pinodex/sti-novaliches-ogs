<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Admin;

use Silex\Application;
use App\Services\Auth;
use App\Services\View;
use App\Services\Form;
use App\Services\Session\FlashBag;
use App\Models\Admin;
use App\Models\Department;
use App\Constraints as CustomAssert;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin controller
 * 
 * Route controllers for /admin/departments/
 */
class DepartmentsController
{
    /**
     * Admin departments page index
     * 
     * URL: /admin/departments/
     */
    public function index()
    {
        $departments = Department::with('head', 'faculties')->get()->toArray();

        return View::render('admin/departments/index', array(
            'departments' => $departments
        ));
    }

    /**
     * Admin departments page edit
     * 
     * URL: /admin/departments/add
     * URL: /admin/departments/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $department = Department::findOrNew($id);

        if ($department->id != $id) {
            FlashBag::add('messages', 'danger>>>Department not found');
            return $app->redirect($app->path('admin.departments.add'));
        }

        $form = Form::create();

        $form->add('name', 'text', array(
            'label' => 'Department Name'
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $department->fill($form->getData());
            $department->save();

            FlashBag::add('messages', 'success>>>Department has been saved');
            return $app->redirect($app->path('admin.departments'));
        }

        return View::render('admin/departments/' . $mode, array(
            'department'    => $department,
            'form'          => $form->createView()
        ));
    }
}
