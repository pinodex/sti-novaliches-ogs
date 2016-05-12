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
use App\Models\Guidance;
use App\Services\View;
use App\Services\Form;
use App\Services\FlashBag;
use App\Constraints as CustomAssert;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Route controller for guidance management pages
 */
class GuidanceController
{
    /**
     * Manage guidance accounts page
     * 
     * URL: /dashboard/guidance/
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
        $result = Guidance::search(null, $request->query->get('name'));

        return View::render('dashboard/guidance/index', array(
            'search_form'   => $form->createView(),
            'result'        => $result->toArray()
        ));
    }

    /**
     * Edit guidance account page
     * 
     * URL: /dashboard/guidance/add
     * URL: /dashboard/guidance/{id}/edit
     */
    public function edit(Request $request, Application $app, $id)
    {
        $mode = 'add';
        $guidance = Guidance::findOrNew($id);

        if ($guidance->id != $id) {
            FlashBag::add('messages', 'danger>>>Guidance account not found');
            return $app->redirect($app->path('dashboard.guidance'));
        }

        $id && $mode = 'edit';
        $form = Form::create($guidance->toArray());

        $form->add('first_name', 'text');
        $form->add('middle_name', 'text');
        $form->add('last_name', 'text');

        $form->add('username', 'text', array(
            'constraints' => new CustomAssert\UniqueRecord(array(
                'model'     => 'App\Models\Guidance',
                'exclude'   => $guidance->username,
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

            $guidance->fill($data);
            $guidance->save();

            FlashBag::add('messages', 'success>>>Guidance account has been saved');

            return $app->redirect($app->path('dashboard.guidance'));
        }

        return View::render('dashboard/guidance/' . $mode, array(
            'form' => $form->createView(),
            'guidance' => $guidance->toArray()
        ));
    }

    /**
     * Delete guidance account page
     * 
     * URL: /dashboard/guidance/{id}/delete
     */
    public function delete(Request $request, Application $app, $id)
    {
        if (!$guidance = Guidance::find($id)) {
            FlashBag::add('messages', 'danger>>>Guidance account not found');

            return $app->redirect($app->path('dashboard.guidance'));
        }

        $form = Form::create();
        $form->add('_confirm', 'hidden');

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $guidance->delete();

            FlashBag::add('messages', 'info>>>Guidance account has been deleted');

            return $app->redirect($app->path('dashboard.guidance'));
        }

        return View::render('dashboard/guidance/delete', array(
            'form' => $form->createView(),
            'guidance' => $guidance->toArray()
        ));
    } 
}
