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
use App\Models\Department;
use App\Models\Head;
use App\Services\View;
use App\Services\Form;
use App\Services\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for head management pages
 */
class HeadsController
{
    /**
     * Manage head accounts page
     * 
     * URL: /dashboard/heads/
     */
    public function index(Request $request)
    {
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
        $result = Head::search(null, $request->query->get('name'));

        return View::render('dashboard/heads/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Edit head account page
     * 
     * URL: /dashboard/heads/add
     * URL: /dashboard/heads/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $head = Head::findOrNew($id);

        if ($head->id != $id) {
            FlashBag::add('messages', 'danger>>>Head account not found');
            return $app->redirect($app->path('dashboard.heads'));
        }

        $id && $mode = 'edit';
        $form = Form::create($head->toArray());
        
        $departments = Department::getFormChoices();
        $departments['0'] = 'Unassigned';

        $form->add('first_name', 'text');
        $form->add('middle_name', 'text');
        $form->add('last_name', 'text');

        $form->add('department_id', 'choice', array(
            'label'         => 'Department',
            'choices'       => $departments,
            'data'          => $head->department_id ?: '0',
            'constraints'   => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Head',
                'exclude'   => $head->department_id,
                'row'       => 'department_id',
                'message'   => 'This department has head already assigned.'
            ))
        ));

        $form->add('username', 'text', array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Head',
                'exclude'   => $head->username,
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
            ),

            'constraints' => new Assert\Length(array(
                'min'        => 8,
                'minMessage' => 'Password should be at least 8 characters long'
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($data['department_id'] == '0') {
                $data['department_id'] = null;
            }

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $head->fill($data);
            $head->save();

            FlashBag::add('messages', 'success>>>Head account has been saved');

            return $app->redirect($app->path('dashboard.heads'));
        }

        return View::render('dashboard/heads/' . $mode, array(
            'form' => $form->createView(),
            'head' => $head->toArray()
        ));
    }

    /**
     * Delete head account page
     * 
     * URL: /dashboard/heads/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$head = Head::find($id)) {
            FlashBag::add('messages', 'danger>>>Head account not found');

            return $app->redirect($app->path('dashboard.heads'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $head->delete();

            FlashBag::add('messages', 'info>>>Head account has been deleted');

            return $app->redirect($app->path('dashboard.heads'));
        }

        return View::render('dashboard/heads/delete', array(
            'form' => $form->createView(),
            'head' => $head->toArray()
        ));
    } 
}
