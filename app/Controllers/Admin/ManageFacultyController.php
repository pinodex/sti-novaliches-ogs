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
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Admin;
use App\Services\View;
use App\Services\Form;
use App\Services\OmegaSheet;
use App\Services\Session\Session;
use App\Services\Session\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints as Assert;
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
    public function manageFaculty(Request $request, Application $app)
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

        $result = Faculty::search(
            $request->query->get('name')
        );

        return View::render('admin/manage/faculty/index', array(
            'search_form' => $form->createView(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage(),
            'result' => $result
        ));
    }

    /**
     * Add/Edit faculty account page
     * 
     * URL: /admin/manage/faculty/add
     * URL: /admin/manage/faculty/{id}/edit
     */
    public function editFaculty(Request $request, Application $app, $id)
    {
        $mode = 'edit';

        if ($id && !$faculty = Faculty::find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');
            return $app->redirect($app->path('admin.manage.faculty'));
        }

        if (!$id) {
            $mode = 'add';
            $faculty = new Faculty();
        }

        $form = Form::create($faculty->toArray());

        $form->add('first_name', Type\TextType::class);
        $form->add('middle_name', Type\TextType::class);
        $form->add('last_name', Type\TextType::class);
        $form->add('department', Type\ChoiceType::class, array(
            'choices' => array(
                'Accounting Technology' => 'Accounting Technology',
                'Business Management' => 'Business Management',
                'General Education' => 'General Education',
                'Hotel and Restaurant Management' => 'Hotel and Restaurant Management',
                'Information Technology' => 'Information Technology'
            )
        ));

        $form->add('username', Type\TextType::class, array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => Faculty::class,
                'exclude'   => $faculty->username,
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

            if ($data['password'] == null) {
                unset($data['password']);
            }

            $faculty->fill($data);
            $faculty->save();

            FlashBag::add('messages', 'success>>>Faculty account has been saved');

            return $app->redirect($app->path('admin.manage.faculty'));
        }

        return View::render('admin/manage/faculty/' . $mode, array(
            'manage_form' => $form->createView(),
            'faculty' => $faculty->toArray()
        ));
    }

    /**
     * Delete faculty account page
     * 
     * URL: /admin/manage/faculty/{id}/delete
     */
    public function deleteFaculty(Request $request, Application $app, $id)
    {
        if (!$faculty = Faculty::find($id)) {
            FlashBag::add('messages', 'danger>>>Faculty account not found');

            return $app->redirect($app->path('admin.manage.faculty'));
        }

        $form = Form::create();
        $form->add('_confirm', Type\HiddenType::class);

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $faculty->delete();

            FlashBag::add('messages', 'info>>>Faculty account has been deleted');

            return $app->redirect($app->path('admin.manage.faculty'));
        }

        return View::render('admin/manage/faculty/delete', array(
            'manage_form' => $form->createView(),
            'faculty' => $faculty->toArray()
        ));
    }
}
