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
use App\Models\Admin;
use App\Services\View;
use App\Services\Form;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin manage admin controller
 * 
 * Route controllers for /admin/manage/admin*
 */
class ManageAdminController
{
    /**
     * Manage admin accounts page
     * 
     * URL: /admin/manage/admin
     */
    public function manageAdmin(Request $request, Application $app)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function() use($page) {
                return $page;
            });
        }

        $form = Form::create(null, array(
            'csrf_protection' => false
        ));
        
        $form->add('name', Type\TextType::class, array(
            'label' => 'Name',
            'required' => false,
            'data' => $request->query->get('name')
        ));

        $form = $form->getForm();

        $result = Admin::search(
            $request->query->get('name')
        );

        return View::render('admin/manage/admin/index', array(
            'search_form' => $form->createView(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'result' => $result
        ));
    }

    /**
     * Edit admin account page
     * 
     * URL: /admin/manage/admin/{id}/edit
     */
    public function editAdmin(Request $request, Application $app, $id)
    {
        $mode = 'edit';

        if ($id && !$admin = Admin::find($id)) {
            FlashBag::add('messages', 'danger>>>Admin account not found');

            return $app->redirect($app->path('admin.manage.admin'));
        }

        if (!$id) {
            $mode = 'add';
            $admin = new Admin();
        }

        $form = Form::create($admin->toArray());

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);
        $form->add('department', Type\ChoiceType::class, array(
            'choices' => array(
                'Accounting Technology Head' => 'Accounting Technology Head',
                'Business Management Head' => 'Business Management Head',
                'General Education Head' => 'General Education Head',
                'Hotel and Restaurant Management Head' => 'Hotel and Restaurant Management Head',
                'Information Technology Head' => 'Information Technology Head'
            )
        ));

        $form->add('username', Type\TextType::class, array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => Admin::class,
                'exclude'   => $admin->username,
                'row'       => 'username',
                'message'   => 'Username already in use.'
            ))
        ));

        $form->add('password', Type\RepeatedType::class, array(
            'type' => Type\PasswordType::class,
            'required' => false,

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

            $admin->fill($data);
            $admin->save();

            FlashBag::add('messages', 'success>>>Admin account has been saved');

            return $app->redirect($app->path('admin.manage.admin'));
        }

        return View::render('admin/manage/admin/' . $mode, array(
            'manage_form' => $form->createView(),
            'admin' => $admin->toArray()
        ));
    }

    /**
     * Delete admin account page
     * 
     * URL: /admin/manage/admin/{id}/delete
     */
    public function deleteAdmin(Request $request, Application $app, $id)
    {
        if (!$admin = Admin::find($id)) {
            FlashBag::add('messages', 'danger>>>Admin account not found');

            return $app->redirect($app->path('admin.manage.admin'));
        }

        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $admin->delete();

            FlashBag::add('messages', 'info>>>Admin account has been deleted');

            return $app->redirect($app->path('admin.manage.admin'));
        }

        return View::render('admin/manage/admin/delete', array(
            'manage_form' => $form->createView(),
            'admin' => $admin->toArray()
        ));
    }
}
