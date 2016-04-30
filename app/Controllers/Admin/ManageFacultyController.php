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
use App\Models\Faculty;
use App\Models\Department;
use App\Services\View;
use App\Services\Form;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manage faculty controller
 * 
 * Route controllers for /admin/manage/faculty/*
 */
class ManageFacultyController
{
    /**
     * Manage faculty accounts page
     * 
     * URL: /admin/manage/faculty
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function() use($page) {
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

        $result = Faculty::search(
            $request->query->get('name')
        );

        return View::render('admin/manage/faculty/index', array(
            'search_form'   => $form->createView(),
            'current_page'  => $result->currentPage(),
            'last_page'     => $result->lastPage(),
            'result'        => $result
        ));
    }

    /**
     * Add/Edit faculty account page
     * 
     * URL: /admin/manage/faculty/add
     * URL: /admin/manage/faculty/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $faculty = Faculty::findOrNew($id);

        if ($faculty->id != $id) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');
            return $app->redirect($app->path('admin.manage.faculty'));
        }

        $id && $mode = 'edit';
        $form = Form::create($faculty->toArray());

        $form->add('first_name', 'text');
        $form->add('middle_name', 'text');
        $form->add('last_name', 'text');
        $form->add('department_id', 'choice', array(
            'label'     => 'Department',
            'choices'   => Department::getFormChoices(),
            'data'      => $faculty->department ? $faculty->department->id : null
        ));

        $form->add('username', 'text', array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Faculty',
                'exclude'   => $faculty->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ))
        ));

        $form->add('password', 'repeated', array(
            'type'      => 'password',
            'required'  => false,

            'first_options' => array(
                'label' => 'Password (leave blank if not changing)'
            ),

            'second_options' => array(
                'label' => 'Repeat Password (leave blank if not changing)'
            )
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $faculty->fill($data);
            $faculty->save();

            FlashBag::add('messages', 'success>>>Faculty account has been saved');

            return $app->redirect($app->path('admin.manage.faculty'));
        }

        return View::render('admin/manage/faculty/' . $mode, array(
            'form'      => $form->createView(),
            'faculty'   => $faculty->toArray()
        ));
    }

    /**
     * Delete faculty account page
     * 
     * URL: /admin/manage/faculty/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$faculty = Faculty::find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');

            return $app->redirect($app->path('admin.manage.faculty'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $faculty->delete();

            FlashBag::add('messages', 'info>>>Faculty account has been deleted');

            return $app->redirect($app->path('admin.manage.faculty'));
        }

        return View::render('admin/manage/faculty/delete', array(
            'form'      => $form->createView(),
            'faculty'   => $faculty->toArray()
        ));
    }
}
