<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controllers\Dashboard;

use Silex\Application;
use App\Services\View;
use App\Services\Form;
use App\Models\Head;
use App\Models\Department;
use App\Services\FlashBag;
use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for departmentistrator management pages
 */
class DepartmentsController extends Controller
{
    /**
     * Admin departments page index
     * 
     * URL: /dashboard/departments/
     */
    public function index()
    {
        $departments = Department::with('head')->get();

        return View::render('dashboard/departments/index', array(
            'departments' => $departments->toArray()
        ));
    }

    /**
     * My department page
     * 
     * URL: /dashboard/departments/self
     */
    public function self(Application $app)
    {
        if ($this->isRole('head') && $this->user->getModel()->department === null) {
            FlashBag::add('messages', 'danger>>>You are not yet assigned to any department');
            return $app->redirect($app->path('dashboard.index'));
        }

        return $app->redirect($app->path('dashboard.departments.view', array(
            'id' => $this->user->getModel()->department->id
        )));
    }

    /**
     * Admin department view page
     * 
     * URL: /dashboard/departments/{id}
     */
    public function view(Request $request, Application $app, $id)
    {
        $department = Department::with('head')->find($id);

        if (!$department) {
            $app->abort(404);
        }

        if ($this->isRole('head') && $this->user->getModel()->department === null || (
            $this->user->getModel()->department !== null &&
            $this->user->getModel()->department->id != $id
        )) {
            $app->abort(403);
        }

        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('name', 'text', array(
            'label'     => 'Name',
            'required'  => false,
            'data'      => $request->query->get('name')
        ));

        $form = $form->getForm();
        $faculty = $department->searchRelated('faculty', null, $request->query->get('name'));

        return View::render('dashboard/departments/view', array(
            'department'    => $department->toArray(),
            'search_form'   => $form->createView(),
            'result'        => $faculty->toArray()
        ));
    }

    /**
     * Admin departments page edit
     * 
     * URL: /dashboard/departments/add
     * URL: /dashboard/departments/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $department = Department::findOrNew($id);

        if ($department->id != $id) {
            $app->abort(404);
        }

        $id && $mode = 'edit';
        $form = Form::create($department->toArray());

        $heads = Head::getFormChoices();
        $heads['0'] = 'No assignment';

        $form->add('name', 'text', array(
            'label' => 'Department Name'
        ));

        $form->add('head', 'choice', array(
            'choices'   => $heads,
            'data'      => $department->head ? $department->head->id : 0
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $department->name = $data['name'];
            $department->save();

            if ($data['head'] != 0) {
                $head = Head::find($data['head']);
                
                $head->department_id = $department->id;
                $head->save();
            } else {
                $head = Head::where('department_id', $department->id)->update(array(
                    'department_id' => null
                ));
            }

            FlashBag::add('messages', 'success>>>Department has been saved');

            if ($mode == 'add') {
                return $app->redirect($app->path('dashboard.departments'));
            }
            
            return $app->redirect($app->path('dashboard.departments.view', array(
                'id' => $department->id
            )));
        }

        return View::render('dashboard/departments/' . $mode, array(
            'department'    => $department,
            'form'          => $form->createView()
        ));
    }

    /**
     * Delete department page
     * 
     * URL: /dashboard/departments/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$department = Department::find($id)) {
            $app->abort(404);
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid() || $this->isTokenValid('departments.delete', $request)) {
            $department->delete();

            FlashBag::add('messages', 'info>>>Department has been deleted');

            return $app->redirect($app->path('dashboard.departments'));
        }

        return View::render('dashboard/departments/delete', array(
            'form'          => $form->createView(),
            'department'    => $department->toArray()
        ));
    } 
}
