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
use App\Models\Admin;
use App\Services\View;
use App\Services\Form;
use App\Services\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for administrator management pages
 */
class AdminsController
{
    /**
     * Manage admin accounts page
     * 
     * URL: /dashboard/admins/
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
        $result = Admin::search(null, $request->query->get('name'));

        return View::render('dashboard/admins/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Edit admin account page
     * 
     * URL: /dashboard/admins/add
     * URL: /dashboard/admins/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $admin = Admin::findOrNew($id);

        if ($admin->id != $id) {
            FlashBag::add('messages', 'danger>>>Admin account not found');
            return $app->redirect($app->path('dashboard.admins'));
        }

        $id && $mode = 'edit';
        $form = Form::create($admin->toArray());

        $form->add('first_name', 'text');
        $form->add('middle_name', 'text');
        $form->add('last_name', 'text');

        $form->add('username', 'text', array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Admin',
                'exclude'   => $admin->username,
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

            if ($data['password'] === null) {
                unset($data['password']);
            }

            $admin->fill($data);
            $admin->save();

            FlashBag::add('messages', 'success>>>Admin account has been saved');

            return $app->redirect($app->path('dashboard.admins'));
        }

        return View::render('dashboard/admins/' . $mode, array(
            'form'  => $form->createView(),
            'admin' => $admin->toArray()
        ));
    }

    /**
     * Delete admin account page
     * 
     * URL: /dashboard/admins/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$admin = Admin::find($id)) {
            FlashBag::add('messages', 'danger>>>Admin account not found');

            return $app->redirect($app->path('dashboard.admins'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $admin->delete();

            FlashBag::add('messages', 'info>>>Admin account has been deleted');

            return $app->redirect($app->path('dashboard.admins'));
        }

        return View::render('dashboard/admins/delete', array(
            'form'  => $form->createView(),
            'admin' => $admin->toArray()
        ));
    } 
}
