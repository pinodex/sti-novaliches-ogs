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
use App\Models\Memo;
use App\Models\Admin;
use App\Models\Faculty;
use App\Services\View;
use App\Services\Form;
use App\Services\FlashBag;
use App\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for memos management pages
 */
class MemosController extends Controller
{
    /**
     * Memos index page
     * 
     * URL: /dashboard/memos/
     */
    public function index(Request $request)
    {
        if ($page = $request->query->get('page')) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }

        $context = array();
        $facultyChoices = Faculty::getFormChoices();

        $searchForm = Form::create($request->query->all(), array(
            'csrf_protection' => false
        ));
        
        $searchForm->add('subject', 'text', array(
            'required'  => false
        ));

        if (!$this->isRole('faculty')) {
            $searchForm->add('admin', 'choice', array(
                'label'     => 'By admin',
                'required'  => false,
                'choices'   => Admin::getFormChoices()
            ));

            $searchForm->add('faculty', 'choice', array(
                'label'     => 'To faculty',
                'required'  => false,
                'choices'   => $facultyChoices
            ));
        }

        $context['search_form'] = $searchForm->getForm()->createView();

        if ($this->isRole('admin')) {
            $composeForm = Form::create(null, array(
                'csrf_protection' => false
            ));

            $composeForm->add('recipient', 'choice', array(
                'choices'       => $facultyChoices,
                'placeholder'   => 'Select recipient'
            ));

            $context['compose_form'] = $composeForm->getForm()->createView();
        }
        
        $subject = $request->query->get('subject');
        $adminId = $request->query->get('admin');
        $facultyId = $request->query->get('faculty');
        
        if ($this->isRole('faculty')) {
            $adminId = null;
            $facultyId = $this->user->getModel()->id;
        }

        $context['result'] = Memo::search($subject, $adminId, $facultyId)->toArray();

        return View::render('dashboard/memos/index', $context);
    }

    /**
     * Memo add page
     * 
     * URL: /dashboard/memos/add
     */
    public function send(Request $request, Application $app)
    {
        if (!$recipient = $request->query->get('recipient')) {
            FlashBag::add('messages', 'danger>>>No valid recipient');
            return $app->redirect($app->path('dashboard.memos'));
        }

        if (!$faculty = Faculty::find($recipient)) {
            FlashBag::add('messages', 'danger>>>Invalid recipient');
            return $app->redirect($app->path('dashboard.memos'));
        }

        $form = Form::create();

        $form->add('subject', 'text', array(
            'data' => 'Failure to submit reports and deliverables'
        ));

        $form->add('content', 'textarea', array(
            'attr' => array(
                'class' => 'medium-editable'
            ),

            'data' => View::simpleRender('_templates/faculty-memo', array(
                'to'    => $faculty->name,
                'from'  => isset($faculty->department->head->name) ? $faculty->department->head->name : 'Department Head',
                'date'  => date('F j, Y')
            ))
        ));

        $form = $form->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if ($this->isRole('admin')) {
                $data['admin_id'] = $this->user->getModel()->id;
            }

            $data['faculty_id'] = $faculty->id;

            Memo::create($data);

            FlashBag::add('messages', 'success>>>Memo sent');
            return $app->redirect($app->path('dashboard.memos'));
        }

        return View::render('dashboard/memos/send', array(
            'form'              => $form->createView(),
            'recipient_name'    => $faculty->name,
            'use_medium_editor' => true
        ));
    }

    /**
     * Memo add page
     * 
     * URL: /dashboard/memos/{id}
     */
    public function view(Request $request, Application $app, $id)
    {
        if (!$memo = Memo::with('admin', 'faculty')->find($id)) {
            $app->abort(404);
        }

        if ($this->isRole('faculty') && $this->user->getModel()->id != $memo->faculty->id) {
            $app->abort(403);
        }

        if ($this->isRole('faculty') && $memo->opened_at === null) {
            $memo->opened_at = date('Y-m-d H:i:s');
            $memo->save();
        }

        return View::render('dashboard/memos/view', array(
            'memo' => $memo->toArray()
        ));
    }
}
